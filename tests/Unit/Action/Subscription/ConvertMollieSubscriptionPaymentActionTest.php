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

namespace Tests\Sylius\MolliePlugin\Unit\Action\Subscription;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Payum\Action\Subscription\ConvertMollieSubscriptionPaymentAction;
use Sylius\MolliePlugin\Payum\Request\CreateCustomer;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;

final class ConvertMollieSubscriptionPaymentActionTest extends TestCase
{
    private ConvertMollieSubscriptionPaymentAction $action;

    private GatewayInterface $gateway;

    protected function setUp(): void
    {
        $divisorProvider = $this->createMock(DivisorProviderInterface::class);
        $divisorProvider->method('getDivisorForCurrency')->willReturn(100);

        $intToStringConverter = $this->createMock(IntToStringConverterInterface::class);
        $intToStringConverter->method('convertIntToString')->willReturn('10.00');

        $this->action = new ConvertMollieSubscriptionPaymentAction(
            $this->createMock(PaymentDescriptionProviderInterface::class),
            $this->createMock(RepositoryInterface::class),
            $this->createMock(OrderConverterInterface::class),
            $this->createMock(CustomerContextInterface::class),
            $this->createMock(PaymentLocaleResolverInterface::class),
            $intToStringConverter,
            $divisorProvider,
        );

        $this->gateway = $this->createMock(GatewayInterface::class);
        $this->gateway->method('execute')->willReturnCallback(static function ($request): void {
            if ($request instanceof GetCurrency) {
                $request->code = 'EUR';
            }
            if ($request instanceof CreateCustomer) {
                $model = $request->getModel();
                $model['customer_mollie_id'] = 'cst_abc';
            }
        });
        $this->action->setGateway($this->gateway);
        $this->action->setApi($this->createMock(MollieApiClient::class));
    }

    public function testItDoesNotFailWhenCartTokenIsAbsentFromPaymentDetails(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getId')->willReturn(1);
        $customer->method('getFullName')->willReturn('Jane Doe');
        $customer->method('getEmail')->willReturn('jane@example.com');

        $order = $this->createMock(OrderInterface::class);
        $order->method('getRecurringSequenceIndex')->willReturn(0);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getNumber')->willReturn('000001');
        $order->method('getId')->willReturn(10);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getOrder')->willReturn($order);
        $payment->method('getCurrencyCode')->willReturn('EUR');
        $payment->method('getAmount')->willReturn(1000);
        // payment details deliberately WITHOUT 'cartToken' (off-session renewal)
        $payment->method('getDetails')->willReturn([
            'recurring' => true,
            'mandateId' => 'mdt_xyz',
            'metadata' => ['molliePaymentMethods' => null],
        ]);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getGatewayName')->willReturn('mollie');

        $request = new Convert($payment, 'array', $token);

        $this->action->execute($request);

        $result = $request->getResult();
        self::assertSame('recurring', $result['sequenceType']);
        self::assertSame('mdt_xyz', $result['mandateId']);
        self::assertSame('cst_abc', $result['customerId']);
    }

    public function testItCarriesTheTrackedMollieSessionOverIntoTheConvertedDetails(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getId')->willReturn(1);
        $customer->method('getFullName')->willReturn('Jane Doe');
        $customer->method('getEmail')->willReturn('jane@example.com');

        $order = $this->createMock(OrderInterface::class);
        $order->method('getRecurringSequenceIndex')->willReturn(0);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getNumber')->willReturn('000001');
        $order->method('getId')->willReturn(10);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getOrder')->willReturn($order);
        $payment->method('getCurrencyCode')->willReturn('EUR');
        $payment->method('getAmount')->willReturn(1000);
        $payment->method('getDetails')->willReturn([
            'metadata' => ['molliePaymentMethods' => 'ideal', 'refund_token' => 'refund_hash'],
            'payment_mollie_id' => 'tr_tracked',
            'webhookUrl' => 'https://shop.example.com/payment/notify/hash',
            'backurl' => 'https://shop.example.com/payment/capture/hash',
        ]);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getGatewayName')->willReturn('mollie_subscription');

        $request = new Convert($payment, 'array', $token);

        $this->action->execute($request);

        $result = $request->getResult();
        self::assertSame('tr_tracked', $result['payment_mollie_id']);
        self::assertSame('https://shop.example.com/payment/notify/hash', $result['webhookUrl']);
        self::assertSame('https://shop.example.com/payment/capture/hash', $result['backurl']);
        self::assertSame('refund_hash', $result['metadata']['refund_token']);
    }
}
