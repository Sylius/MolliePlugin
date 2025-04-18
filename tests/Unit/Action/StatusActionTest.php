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

use Exception;
use Mollie\Api\Endpoints\CustomerEndpoint;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Types\SubscriptionStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetStatusInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Action\StatusAction;
use Sylius\MolliePlugin\Refund\Checker\MollieOrderRefundCheckerInterface;
use Sylius\MolliePlugin\Refund\OrderRefundInterface;
use Sylius\MolliePlugin\Refund\PaymentRefundInterface;
use Sylius\MolliePlugin\Voucher\Updater\OrderVoucherAdjustmentUpdaterInterface;

final class StatusActionTest extends TestCase
{
    private StatusAction $statusAction;

    private PaymentRefundInterface $paymentRefund;

    private OrderRefundInterface $orderRefund;

    private MollieLoggerActionInterface $loggerAction;

    private OrderVoucherAdjustmentUpdaterInterface $orderVoucherAdjustmentUpdater;

    private GatewayInterface $gateway;

    private MollieApiClient $mollieApiClient;

    private MollieOrderRefundCheckerInterface $mollieOrderRefundChecker;

    protected function setUp(): void
    {
        $this->paymentRefund = $this->createMock(PaymentRefundInterface::class);
        $this->orderRefund = $this->createMock(OrderRefundInterface::class);
        $this->loggerAction = $this->createMock(MollieLoggerActionInterface::class);
        $this->orderVoucherAdjustmentUpdater = $this->createMock(OrderVoucherAdjustmentUpdaterInterface::class);
        $this->gateway = $this->createMock(GatewayInterface::class);
        $this->mollieApiClient = $this->createMock(MollieApiClient::class);
        $this->mollieOrderRefundChecker = $this->createMock(MollieOrderRefundCheckerInterface::class);

        $this->statusAction = new StatusAction(
            $this->paymentRefund,
            $this->orderRefund,
            $this->loggerAction,
            $this->orderVoucherAdjustmentUpdater,
            $this->mollieOrderRefundChecker,
        );

        $this->statusAction->setApi($this->mollieApiClient);
        $this->statusAction->setGateway($this->gateway);
    }

    public function testItImplementsActionInterface(): void
    {
        $this->assertInstanceOf(ActionInterface::class, $this->statusAction);
    }

    public function testItImplementsApiAwareInterface(): void
    {
        $this->assertInstanceOf(ApiAwareInterface::class, $this->statusAction);
    }

    public function testItImplementsGatewayAwareInterface(): void
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, $this->statusAction);
    }

    public function testItMarksPaymentAsNewWhenDetailsAreUnset(): void
    {
        $request = $this->createMock(GetStatusInterface::class);
        $payment = $this->createMock(PaymentInterface::class);

        $payment->method('getDetails')->willReturn([]);
        $payment->method('getId')->willReturn(1);
        $request->method('getModel')->willReturn($payment);

        $request->expects($this->once())->method('markNew');
        $this->loggerAction->expects($this->once())
            ->method('addLog')
            ->with($this->stringContains('Mark new payment with id 1'))
        ;

        $this->statusAction->execute($request);
    }

    public function testItMarksPaymentAsFailedWhenStatusErrorIsPresent(): void
    {
        $request = $this->createMock(GetStatusInterface::class);
        $payment = $this->createMock(PaymentInterface::class);

        $details = ['statusError' => 1];
        $payment->method('getDetails')->willReturn($details);
        $payment->method('getId')->willReturn(1);
        $request->method('getModel')->willReturn($payment);

        $request->expects($this->once())->method('markFailed');

        $this->statusAction->execute($request);
    }

    public function testItThrowsApiExceptionWhenFetchingCustomerFails(): void
    {
        $request = $this->createMock(GetStatusInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $customerEndpoint = $this->createMock(CustomerEndpoint::class);

        $details = ['subscription_mollie_id' => 1, 'customer_mollie_id' => 1];
        $payment->method('getDetails')->willReturn($details);
        $payment->method('getId')->willReturn(1);
        $request->method('getModel')->willReturn($payment);

        $this->mollieApiClient->customers = $customerEndpoint;
        $customerEndpoint->method('get')->willThrowException(new Exception('API error'));

        $this->loggerAction->expects($this->once())
            ->method('addNegativeLog')
            ->with($this->stringContains('Error with get customer from mollie'))
        ;

        $this->expectException(ApiException::class);

        $this->statusAction->execute($request);
    }

    public function testItMarksSubscriptionAsCanceled(): void
    {
        $request = $this->createMock(GetStatusInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $customerEndpoint = $this->createMock(CustomerEndpoint::class);
        $customer = $this->createMock(Customer::class);
        $subscription = $this->createMock(Subscription::class);

        $details = ['subscription_mollie_id' => 1, 'customer_mollie_id' => 1];
        $payment->method('getDetails')->willReturn($details);
        $payment->method('getId')->willReturn(1);
        $request->method('getModel')->willReturn($payment);

        $this->mollieApiClient->customers = $customerEndpoint;
        $customerEndpoint->method('get')->willReturn($customer);
        $customer->method('getSubscription')->willReturn($subscription);
        $subscription->status = SubscriptionStatus::STATUS_CANCELED;

        $request->expects($this->once())->method('markCanceled');
        $this->loggerAction->expects($this->once())
            ->method('addLog')
            ->with($this->stringContains('Mark subscription status to: canceled'))
        ;

        $this->statusAction->execute($request);
    }
}
