<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Unit\Creator;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\MolliePlugin\Creator\PaymentDataCreator;
use Sylius\MolliePlugin\Creator\PaymentDataCreatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface;
use Sylius\MolliePlugin\Repository\MollieGatewayConfigRepositoryInterface;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PaymentDataCreatorTest extends TestCase
{
    private IntToStringConverterInterface $intToStringConverter;

    private UrlGeneratorInterface $router;

    private PaymentDescriptionProviderInterface $paymentDescriptionProvider;

    private PaymentLocaleResolverInterface $paymentLocaleResolver;

    private DivisorProviderInterface $divisorProvider;

    private MollieGatewayConfigRepositoryInterface $mollieGatewayConfigRepository;

    private OrderConverterInterface $orderConverter;

    private PaymentDataCreator $paymentDataCreator;

    protected function setUp(): void
    {
        $this->intToStringConverter = $this->createMock(IntToStringConverterInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $this->paymentDescriptionProvider = $this->createMock(PaymentDescriptionProviderInterface::class);
        $this->paymentLocaleResolver = $this->createMock(PaymentLocaleResolverInterface::class);
        $this->divisorProvider = $this->createMock(DivisorProviderInterface::class);
        $this->mollieGatewayConfigRepository = $this->createMock(MollieGatewayConfigRepositoryInterface::class);
        $this->orderConverter = $this->createMock(OrderConverterInterface::class);

        $this->paymentDataCreator = new PaymentDataCreator(
            $this->intToStringConverter,
            $this->router,
            $this->paymentDescriptionProvider,
            $this->paymentLocaleResolver,
            $this->divisorProvider,
            $this->mollieGatewayConfigRepository,
            $this->orderConverter,
            'en_US',
        );
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(PaymentDataCreatorInterface::class, $this->paymentDataCreator);
    }

    public function testCreatesPaymentDataForNonSubscription(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $methodConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $customer = $this->createMock(CustomerInterface::class);

        $this->divisorProvider->expects($this->once())->method('getDivisor')->willReturn(100);

        $gatewayConfig->expects($this->once())->method('getGatewayName')->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findOneActiveByGatewayNameAndMethod')
            ->with('mollie', 'ideal')
            ->willReturn($methodConfig)
        ;

        $order->method('getLocaleCode')->willReturn('nl_NL');
        $order->method('getId')->willReturn(42);
        $order->method('getCustomer')->willReturn($customer);
        $customer->method('getId')->willReturn(7);

        $this->router->expects($this->once())
            ->method('generate')
            ->with('sylius_mollie_shop_payment_webhook', ['_locale' => 'nl_NL'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://shop.example.com/webhook')
        ;

        $payment->method('getCurrencyCode')->willReturn('EUR');
        $payment->method('getAmount')->willReturn(1500);

        $this->intToStringConverter->expects($this->once())
            ->method('convertIntToString')
            ->with(1500, 100)
            ->willReturn('15.00')
        ;

        $this->paymentDescriptionProvider->expects($this->once())
            ->method('getPaymentDescription')
            ->with($payment, $methodConfig, $order)
            ->willReturn('Order #0042')
        ;

        $this->orderConverter->expects($this->once())
            ->method('convert')
            ->with($order, $this->isType('array'), 100, $methodConfig)
            ->willReturn([
                'billingAddress' => ['street' => 'Keizersgracht 1'],
                'shippingAddress' => ['street' => 'Prinsengracht 2'],
                'lines' => [
                    [
                        'name' => 'T-shirt',
                        'type' => 'physical',
                        'quantity' => 2,
                        'unitPrice' => ['value' => '5.00', 'currency' => 'EUR'],
                        'totalAmount' => ['value' => '10.00', 'currency' => 'EUR'],
                        'vatRate' => '21.00',
                        'vatAmount' => ['value' => '1.74', 'currency' => 'EUR'],
                    ],
                ],
            ])
        ;

        $this->paymentLocaleResolver->expects($this->once())
            ->method('resolveFromOrder')
            ->with($order)
            ->willReturn('nl_NL');

        $result = $this->paymentDataCreator->create(
            $order,
            $payment,
            $gatewayConfig,
            'ideal',
            false,
            'https://shop.example.com/back',
            null,
        );

        $this->assertSame('ideal', $result['method']);
        $this->assertSame(['currency' => 'EUR', 'value' => '15.00'], $result['amount']);
        $this->assertSame('Order #0042', $result['description']);
        $this->assertSame('https://shop.example.com/back', $result['redirectUrl']);
        $this->assertSame('https://shop.example.com/webhook', $result['webhookUrl']);
        $this->assertSame(
            ['order_id' => 42, 'customer_id' => 7, 'molliePaymentMethods' => 'ideal'],
            $result['metadata'],
        );
        $this->assertSame(['street' => 'Keizersgracht 1'], $result['billingAddress']);
        $this->assertSame(['street' => 'Prinsengracht 2'], $result['shippingAddress']);
        $this->assertSame('nl_NL', $result['locale']);
        $this->assertArrayNotHasKey('customerId', $result);
        $this->assertArrayNotHasKey('sequenceType', $result);

        $this->assertCount(1, $result['lines']);
        $this->assertSame('T-shirt', $result['lines'][0]['description']);
        $this->assertSame('physical', $result['lines'][0]['type']);
        $this->assertSame(2, $result['lines'][0]['quantity']);
    }

    public function testCreatesPaymentDataForSubscription(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $methodConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $customer = $this->createMock(CustomerInterface::class);

        $this->divisorProvider->method('getDivisor')->willReturn(100);
        $gatewayConfig->method('getGatewayName')->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $this->mollieGatewayConfigRepository->method('findOneActiveByGatewayNameAndMethod')->willReturn($methodConfig);
        $order->method('getLocaleCode')->willReturn('en_US');
        $order->method('getId')->willReturn(1);
        $order->method('getCustomer')->willReturn($customer);
        $customer->method('getId')->willReturn(2);
        $this->router->method('generate')->willReturn('https://shop.example.com/webhook');
        $payment->method('getCurrencyCode')->willReturn('EUR');
        $payment->method('getAmount')->willReturn(2000);
        $this->intToStringConverter->method('convertIntToString')->willReturn('20.00');
        $this->paymentDescriptionProvider->method('getPaymentDescription')->willReturn('Subscription');
        $this->orderConverter->method('convert')->willReturn([
            'billingAddress' => [],
            'shippingAddress' => [],
            'lines' => [],
        ]);
        $this->paymentLocaleResolver->method('resolveFromOrder')->willReturn(null);

        $result = $this->paymentDataCreator->create(
            $order,
            $payment,
            $gatewayConfig,
            'creditcard',
            true,
            'https://shop.example.com/back',
            'cst_mollie123',
        );

        $this->assertSame('cst_mollie123', $result['customerId']);
        $this->assertSame('first', $result['sequenceType']);
        $this->assertSame('first', $result['metadata']['sequenceType']);
    }

    public function testDoesNotIncludeLocaleWhenNull(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $methodConfig = $this->createMock(MollieGatewayConfigInterface::class);

        $this->divisorProvider->method('getDivisor')->willReturn(100);
        $gatewayConfig->method('getGatewayName')->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $this->mollieGatewayConfigRepository->method('findOneActiveByGatewayNameAndMethod')->willReturn($methodConfig);
        $order->method('getLocaleCode')->willReturn('en_US');
        $order->method('getId')->willReturn(1);
        $order->method('getCustomer')->willReturn(null);
        $this->router->method('generate')->willReturn('https://shop.example.com/webhook');
        $payment->method('getCurrencyCode')->willReturn('EUR');
        $payment->method('getAmount')->willReturn(1000);
        $this->intToStringConverter->method('convertIntToString')->willReturn('10.00');
        $this->paymentDescriptionProvider->method('getPaymentDescription')->willReturn('Order');
        $this->orderConverter->method('convert')->willReturn([
            'billingAddress' => [],
            'shippingAddress' => [],
            'lines' => [],
        ]);

        $this->paymentLocaleResolver->expects($this->once())
            ->method('resolveFromOrder')
            ->with($order)
            ->willReturn(null)
        ;

        $result = $this->paymentDataCreator->create(
            $order,
            $payment,
            $gatewayConfig,
            'ideal',
            false,
            'https://shop.example.com/back',
            null,
        );

        $this->assertArrayNotHasKey('locale', $result);
    }

    public function testThrowsExceptionWhenMethodConfigNotFound(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->divisorProvider->method('getDivisor')->willReturn(100);
        $gatewayConfig->method('getGatewayName')->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findOneActiveByGatewayNameAndMethod')
            ->with('mollie', 'unknown_method')
            ->willReturn(null)
        ;

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Mollie method "unknown_method" cannot be selected');

        $this->paymentDataCreator->create(
            $order,
            $payment,
            $gatewayConfig,
            'unknown_method',
            false,
            'https://shop.example.com/back',
            null,
        );
    }

    public function testUsesDefaultLocaleWhenOrderLocaleIsNull(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $methodConfig = $this->createMock(MollieGatewayConfigInterface::class);

        $this->divisorProvider->method('getDivisor')->willReturn(100);
        $gatewayConfig->method('getGatewayName')->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $this->mollieGatewayConfigRepository->method('findOneActiveByGatewayNameAndMethod')->willReturn($methodConfig);
        $order->method('getLocaleCode')->willReturn(null);
        $order->method('getId')->willReturn(1);
        $order->method('getCustomer')->willReturn(null);
        $payment->method('getCurrencyCode')->willReturn('EUR');
        $payment->method('getAmount')->willReturn(1000);
        $this->intToStringConverter->method('convertIntToString')->willReturn('10.00');
        $this->paymentDescriptionProvider->method('getPaymentDescription')->willReturn('Order');
        $this->orderConverter->method('convert')->willReturn([
            'billingAddress' => [],
            'shippingAddress' => [],
            'lines' => [],
        ]);
        $this->paymentLocaleResolver->method('resolveFromOrder')->willReturn(null);

        $this->router->expects($this->once())
            ->method('generate')
            ->with('sylius_mollie_shop_payment_webhook', ['_locale' => 'en_US'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://shop.example.com/webhook')
        ;

        $this->paymentDataCreator->create(
            $order,
            $payment,
            $gatewayConfig,
            'ideal',
            false,
            'https://shop.example.com/back',
            null,
        );
    }

    public function testMapsLineFieldsCorrectly(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $methodConfig = $this->createMock(MollieGatewayConfigInterface::class);

        $this->divisorProvider->method('getDivisor')->willReturn(100);
        $gatewayConfig->method('getGatewayName')->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $this->mollieGatewayConfigRepository->method('findOneActiveByGatewayNameAndMethod')->willReturn($methodConfig);
        $order->method('getLocaleCode')->willReturn('en_US');
        $order->method('getId')->willReturn(1);
        $order->method('getCustomer')->willReturn(null);
        $this->router->method('generate')->willReturn('https://shop.example.com/webhook');
        $payment->method('getCurrencyCode')->willReturn('EUR');
        $payment->method('getAmount')->willReturn(5000);
        $this->intToStringConverter->method('convertIntToString')->willReturn('50.00');
        $this->paymentDescriptionProvider->method('getPaymentDescription')->willReturn('Order');
        $this->paymentLocaleResolver->method('resolveFromOrder')->willReturn(null);

        $this->orderConverter->method('convert')->willReturn([
            'billingAddress' => [],
            'shippingAddress' => [],
            'lines' => [
                [
                    'name' => 'Widget',
                    'type' => 'digital',
                    'quantity' => 3,
                    'unitPrice' => ['value' => '10.00', 'currency' => 'EUR'],
                    'totalAmount' => ['value' => '30.00', 'currency' => 'EUR'],
                    'vatRate' => '21.00',
                    'vatAmount' => ['value' => '5.21', 'currency' => 'EUR'],
                    'extra_field' => 'should_be_excluded',
                ],
                [
                    'quantity' => 1,
                    'unitPrice' => ['value' => '20.00', 'currency' => 'EUR'],
                    'totalAmount' => ['value' => '20.00', 'currency' => 'EUR'],
                    'vatRate' => '0.00',
                    'vatAmount' => ['value' => '0.00', 'currency' => 'EUR'],
                ],
            ],
        ]);

        $result = $this->paymentDataCreator->create(
            $order,
            $payment,
            $gatewayConfig,
            'ideal',
            false,
            'https://shop.example.com/back',
            null,
        );

        $this->assertCount(2, $result['lines']);

        $this->assertSame('Widget', $result['lines'][0]['description']);
        $this->assertSame('digital', $result['lines'][0]['type']);
        $this->assertSame(3, $result['lines'][0]['quantity']);
        $this->assertArrayNotHasKey('extra_field', $result['lines'][0]);

        $this->assertSame('', $result['lines'][1]['description']);
        $this->assertSame('physical', $result['lines'][1]['type']);
        $this->assertSame(1, $result['lines'][1]['quantity']);
    }

    public function testCustomerIdIsNullInMetadataWhenOrderHasNoCustomer(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $methodConfig = $this->createMock(MollieGatewayConfigInterface::class);

        $this->divisorProvider->method('getDivisor')->willReturn(100);
        $gatewayConfig->method('getGatewayName')->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $this->mollieGatewayConfigRepository->method('findOneActiveByGatewayNameAndMethod')->willReturn($methodConfig);
        $order->method('getLocaleCode')->willReturn('en_US');
        $order->method('getId')->willReturn(5);
        $order->method('getCustomer')->willReturn(null);
        $this->router->method('generate')->willReturn('https://shop.example.com/webhook');
        $payment->method('getCurrencyCode')->willReturn('USD');
        $payment->method('getAmount')->willReturn(999);
        $this->intToStringConverter->method('convertIntToString')->willReturn('9.99');
        $this->paymentDescriptionProvider->method('getPaymentDescription')->willReturn('Order');
        $this->orderConverter->method('convert')->willReturn([
            'billingAddress' => [],
            'shippingAddress' => [],
            'lines' => [],
        ]);
        $this->paymentLocaleResolver->method('resolveFromOrder')->willReturn(null);

        $result = $this->paymentDataCreator->create(
            $order,
            $payment,
            $gatewayConfig,
            'bancontact',
            false,
            'https://shop.example.com/back',
            null,
        );

        $this->assertNull($result['metadata']['customer_id']);
        $this->assertSame('USD', $result['amount']['currency']);
        $this->assertSame('9.99', $result['amount']['value']);
    }
}
