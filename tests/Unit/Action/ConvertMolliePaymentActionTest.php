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

namespace Tests\Sylius\MolliePlugin\Unit\Action;

use ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Payum\Action\ConvertMolliePaymentAction;
use Sylius\MolliePlugin\Payum\Factory\CreateCustomerFactoryInterface;
use Sylius\MolliePlugin\Payum\Request\CreateCustomer;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ConvertMolliePaymentActionTest extends TestCase
{
    private PaymentDescriptionProviderInterface $paymentDescriptionProviderMock;

    private SessionInterface $sessionMock;

    private RepositoryInterface $mollieMethodsRepositoryMock;

    private OrderConverterInterface $orderConverterMock;

    private CustomerContextInterface $customerContextMock;

    private PaymentLocaleResolverInterface $paymentLocaleResolverMock;

    private CreateCustomerFactoryInterface $createCustomerFactoryMock;

    private ConvertMolliePaymentAction $convertMolliePaymentAction;

    protected function setUp(): void
    {
        $this->paymentDescriptionProviderMock = $this->createMock(PaymentDescriptionProviderInterface::class);
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->mollieMethodsRepositoryMock = $this->createMock(RepositoryInterface::class);
        $this->orderConverterMock = $this->createMock(OrderConverterInterface::class);
        $this->customerContextMock = $this->createMock(CustomerContextInterface::class);
        $this->paymentLocaleResolverMock = $this->createMock(PaymentLocaleResolverInterface::class);
        $this->createCustomerFactoryMock = $this->createMock(CreateCustomerFactoryInterface::class);
        $this->intToStringConverterMock = $this->createMock(IntToStringConverterInterface::class);
        $this->divisorProviderMock = $this->createMock(DivisorProviderInterface::class);
        $this->convertMolliePaymentAction =
            new ConvertMolliePaymentAction(
                $this->paymentDescriptionProviderMock,
                $this->mollieMethodsRepositoryMock,
                $this->orderConverterMock,
                $this->customerContextMock,
                $this->paymentLocaleResolverMock,
                $this->createCustomerFactoryMock,
                $this->intToStringConverterMock,
                $this->divisorProviderMock,
            )
        ;
    }

    public function testExecutesWhenMetadataAndSingleClickAreEnabled(): void
    {
        $inputDetails = [
            'amount' => [
                'value' => '',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 'ideal',
                'cartToken' => 'carttoken',
                'saveCardInfo' => null,
                'useSavedCards' => null,
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
        ];
        $paymentMock = $this->createMock(PaymentInterface::class);
        $requestMock = new Convert($paymentMock, 'array');
        $orderMock = $this->createMock(OrderInterface::class);
        $customerMock = $this->createMock(CustomerInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);
        $methodMock = $this->createMock(MollieGatewayConfigInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieCustomerMock = $this->createMock(CreateCustomer::class);

        $this->customerContextMock->method('getCustomer')->willReturn($customerMock);
        $paymentMock->method('getDetails')->willReturn($inputDetails);

        $mollieApiClientMock->expects($this->once())->method('isRecurringSubscription')->willReturn(false);
        $this->createCustomerFactoryMock->expects($this->once())->method('createNew')->willReturn($mollieCustomerMock);
        $this->convertMolliePaymentAction->setApi($mollieApiClientMock);
        $this->convertMolliePaymentAction->setGateway($gatewayMock);
        $customerMock->expects($this->once())->method('getFullName')->willReturn('Jan Kowalski');
        $customerMock->expects($this->once())->method('getEmail')->willReturn('shop@example.com');
        $customerMock->expects($this->once())->method('getId')->willReturn(1);
        $orderMock->method('getId')->willReturn(1);
        $orderMock->method('getLocaleCode')->willReturn('pl_PL');
        $orderMock->method('getCustomer')->willReturn($customerMock);
        $paymentMock->method('getOrder')->willReturn($orderMock);
        $paymentMock->method('getAmount')->willReturn(445535);
        $paymentMock->method('getCurrencyCode')->willReturn('EUR');
        $this->paymentDescriptionProviderMock->expects($this->once())->method('getPaymentDescription')->with($paymentMock, $methodMock, $orderMock)->willReturn('description');
        $this->mollieMethodsRepositoryMock->expects($this->once())->method('findOneBy')->with(['methodId' => 'ideal'])->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getPaymentType')->willReturn('payment_type');
        $methodMock->expects($this->once())->method('getGateway')->willReturn($gatewayConfigMock);
        $gatewayConfigMock->expects($this->once())->method('getConfig')->willReturn([
            'single_click_enabled' => true,
        ]);

        $this->createCustomerFactoryMock->expects($this->once())->method('createNew')->with($inputDetails)->willReturn($mollieCustomerMock);
        $mollieCustomerMock->expects($this->once())->method('getModel')->willReturn(new ArrayObject(
            [
                'customer_mollie_id' => 15,
            ],
        ));

        $this->convertMolliePaymentAction->execute($requestMock);
        $inputDetails['metadata']['customer_mollie_id'] = 15;
        $inputDetails['metadata']['methodType'] = 'Payments API';
        $inputDetails['customerId'] = 15;
        $this->assertSame($inputDetails, $requestMock->getResult());
    }

    public function testExecutesWithNoMetadataAndRecurringSubscriptionSetToFalse(): void
    {
        $inputDetails = [
            'amount' => [
                'value' => '',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 15,
                'cartToken' => 'token',
                'saveCardInfo' => null,
                'useSavedCards' => null,
                'methodType' => 'Payments API',
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customerId' => null,
        ];

        $paymentMock = $this->createMock(PaymentInterface::class);
        $requestMock = new Convert($paymentMock, 'array');
        $orderMock = $this->createMock(OrderInterface::class);
        $customerMock = $this->createMock(CustomerInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);
        $methodMock = $this->createMock(MollieGatewayConfigInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $mollieApiClientMock->method('isRecurringSubscription')->willReturn(false);
        $this->convertMolliePaymentAction->setApi($mollieApiClientMock);
        $this->convertMolliePaymentAction->setGateway($gatewayMock);
        $customerMock->expects($this->once())->method('getFullName')->willReturn('Jan Kowalski');
        $customerMock->expects($this->once())->method('getEmail')->willReturn('shop@example.com');
        $customerMock->expects($this->once())->method('getId')->willReturn(1);
        $orderMock->expects($this->once())->method('getId')->willReturn(1);
        $orderMock->method('getLocaleCode')->willReturn('pl_PL');
        $orderMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $paymentMock->method('getAmount')->willReturn(445535);
        $paymentMock->method('getCurrencyCode')->willReturn('EUR');
        $this->paymentDescriptionProviderMock->expects($this->once())->method('getPaymentDescription')->with($paymentMock, $methodMock, $orderMock)->willReturn('description');
        $paymentMock->expects($this->once())->method('getDetails')->willReturn([
            'molliePaymentMethods' => 15,
            'cartToken' => 'token',
        ]);
        $this->mollieMethodsRepositoryMock->expects($this->once())->method('findOneBy')->with(['methodId' => 15])->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getPaymentType')->willReturn('payment_type');
        $methodMock->expects($this->once())->method('getGateway')->willReturn($gatewayConfigMock);
        $gatewayConfigMock->expects($this->once())->method('getConfig')->willReturn([]);

        $this->convertMolliePaymentAction->execute($requestMock);
        $this->assertSame($inputDetails, $requestMock->getResult());
    }

    public function testExecutesWithNoMetadataAndRecurringSubscriptionSetToFalseButWithPaymentLocale(): void
    {
        $inputDetails = [
            'amount' => [
                'value' => '',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 15,
                'cartToken' => 'token',
                'saveCardInfo' => null,
                'useSavedCards' => null,
                'methodType' => 'Payments API',
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customerId' => null,
            'locale' => 'payment_locale',
        ];

        $paymentMock = $this->createMock(PaymentInterface::class);
        $requestMock = new Convert($paymentMock, 'array');
        $orderMock = $this->createMock(OrderInterface::class);
        $customerMock = $this->createMock(CustomerInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);
        $methodMock = $this->createMock(MollieGatewayConfigInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $mollieApiClientMock->expects($this->once())->method('isRecurringSubscription')->willReturn(false);
        $this->convertMolliePaymentAction->setApi($mollieApiClientMock);
        $this->convertMolliePaymentAction->setGateway($gatewayMock);
        $customerMock->expects($this->once())->method('getFullName')->willReturn('Jan Kowalski');
        $customerMock->expects($this->once())->method('getEmail')->willReturn('shop@example.com');
        $customerMock->expects($this->once())->method('getId')->willReturn(1);
        $orderMock->expects($this->once())->method('getId')->willReturn(1);
        $orderMock->method('getLocaleCode')->willReturn('pl_PL');
        $orderMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $paymentMock->method('getAmount')->willReturn(445535);
        $paymentMock->method('getCurrencyCode')->willReturn('EUR');
        $this->paymentDescriptionProviderMock->expects($this->once())->method('getPaymentDescription')->with($paymentMock, $methodMock, $orderMock)->willReturn('description');
        $paymentMock->expects($this->once())->method('getDetails')->willReturn([
            'molliePaymentMethods' => 15,
            'cartToken' => 'token',
        ]);
        $this->mollieMethodsRepositoryMock->expects($this->once())->method('findOneBy')->with(['methodId' => 15])->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getPaymentType')->willReturn('payment_type');
        $methodMock->expects($this->once())->method('getGateway')->willReturn($gatewayConfigMock);
        $gatewayConfigMock->expects($this->once())->method('getConfig')->willReturn([]);
        $this->paymentLocaleResolverMock->expects($this->once())->method('resolveFromOrder')->with($orderMock)->willReturn('payment_locale');

        $this->convertMolliePaymentAction->execute($requestMock);
        $this->assertSame($inputDetails, $requestMock->getResult());
    }

    public function testExecutesWithNoMetadataAndRecurringSubscriptionSetToFalseButWithPaymentLocaleAndOrderApi(): void
    {
        $inputDetails = [
            'amount' => [
                'value' => '',
                'currency' => 'EUR',
            ],
            'description' => 'description',
            'metadata' => [
                'order_id' => 1,
                'customer_id' => 1,
                'molliePaymentMethods' => 15,
                'cartToken' => 'token',
                'methodType' => 'Orders API',
                'saveCardInfo' => null,
                'useSavedCards' => null,
            ],
            'full_name' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'locale' => 'payment_locale',
        ];

        $paymentMock = $this->createMock(PaymentInterface::class);
        $requestMock = new Convert($paymentMock, 'array');
        $orderMock = $this->createMock(OrderInterface::class);
        $customerMock = $this->createMock(CustomerInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);
        $methodMock = $this->createMock(MollieGatewayConfigInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);

        $mollieApiClientMock->expects($this->once())->method('isRecurringSubscription')->willReturn(false);
        $this->convertMolliePaymentAction->setApi($mollieApiClientMock);
        $this->convertMolliePaymentAction->setGateway($gatewayMock);
        $this->mollieMethodsRepositoryMock->expects($this->once())->method('findOneBy')->with(['methodId' => 15])->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getPaymentType')->willReturn('ORDER_API');
        $methodMock->expects($this->once())->method('getGateway')->willReturn($gatewayConfigMock);
        $gatewayConfigMock->expects($this->once())->method('getConfig')->willReturn([]);
        $currency = new GetCurrency('EUR');
        $gatewayMock->expects($this->once())->method('execute')->with($currency);
        $this->divisorProviderMock->method('getDivisorForCurrency')->with($currency)->willReturn(1);
        $customerMock->expects($this->once())->method('getFullName')->willReturn('Jan Kowalski');
        $customerMock->expects($this->once())->method('getEmail')->willReturn('shop@example.com');
        $customerMock->expects($this->once())->method('getId')->willReturn(1);
        $paymentMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $paymentMock->method('getAmount')->willReturn(445535);
        $paymentMock->method('getCurrencyCode')->willReturn('EUR');
        $paymentMock->expects($this->once())->method('getDetails')->willReturn([
            'molliePaymentMethods' => 15,
            'cartToken' => 'token',
        ]);
        $orderMock->expects($this->once())->method('getId')->willReturn(1);
        $orderMock->method('getLocaleCode')->willReturn('pl_PL');
        $orderMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $orderMock->method('getTotal')->willReturn(445535);
        $this->paymentDescriptionProviderMock->expects($this->once())->method('getPaymentDescription')->with($paymentMock, $methodMock, $orderMock)->willReturn('description');
        $this->paymentLocaleResolverMock->expects($this->once())->method('resolveFromOrder')->with($orderMock)->willReturn('payment_locale');

        $this->orderConverterMock->expects($this->once())->method('convert')->with($orderMock, $inputDetails, 1, $methodMock)->willReturn($inputDetails);

        $this->convertMolliePaymentAction->execute($requestMock);
        $this->assertSame($inputDetails, $requestMock->getResult());
    }

    public function testSupportsOnlyConvertRequestWithGetSourceAsInstanceOfPaymentInterfaceAndGetToEqualsArray(): void
    {
        $requestMock = $this->createMock(Convert::class);
        $paymentMock = $this->createMock(PaymentInterface::class);
        $requestMock->expects($this->once())->method('getSource')->willReturn($paymentMock);
        $requestMock->expects($this->once())->method('getTo')->willReturn('array');

        $this->assertTrue($this->convertMolliePaymentAction->supports($requestMock));
    }
}
