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

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Refund;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Action\Refund\RefundAction;
use Sylius\MolliePlugin\Refund\Converter\RefundDataConverterInterface;

final class RefundActionTest extends TestCase
{
    private RefundAction $refundAction;

    private MollieLoggerActionInterface $loggerAction;

    private RefundDataConverterInterface $convertOrderRefundData;

    protected function setUp(): void
    {
        $this->loggerAction = $this->createMock(MollieLoggerActionInterface::class);
        $this->convertOrderRefundData = $this->createMock(RefundDataConverterInterface::class);
        $this->refundAction = new RefundAction($this->loggerAction, $this->convertOrderRefundData);
    }

    public function testItImplementsInterfaces(): void
    {
        $this->assertInstanceOf(ActionInterface::class, $this->refundAction);
        $this->assertInstanceOf(ApiAwareInterface::class, $this->refundAction);
        $this->assertInstanceOf(GatewayAwareInterface::class, $this->refundAction);
    }

    public function testItRefundsActionWhenPaymentIdIsSet(): void
    {
        $request = $this->createMock(Refund::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $payment = $this->createMock(PaymentInterface::class);
        $molliePayment = $this->createMock(Payment::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $this->refundAction->setApi($mollieApiClient);
        $request->method('getFirstModel')->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;
        $payment->method('getCurrencyCode')->willReturn('EUR');

        $details = new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => null,
            'metadata' => ['refund' => ['test_refund', 'items' => ['test_item']]],
        ]);

        $request->method('getModel')->willReturn($details);
        $paymentEndpoint->method('get')->with(4)->willReturn($molliePayment);
        $this->convertOrderRefundData->method('convert')->willReturn(['5']);

        $molliePayment->method('hasRefunds')->willReturn(false);
        $molliePayment->id = '4';
        $molliePayment->amountRemaining = 5;
        $molliePayment->method('canBeRefunded')->willReturn(true);

        $molliePayment->expects($this->once())->method('refund')->with(['amount' => ['5']]);
        $this->loggerAction->expects($this->once())->method('addLog')->with('Refund action with payment id 4');

        $this->refundAction->execute($request);
    }

    public function testItCannotRefund(): void
    {
        $request = $this->createMock(Refund::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $payment = $this->createMock(PaymentInterface::class);
        $molliePayment = $this->createMock(Payment::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $this->refundAction->setApi($mollieApiClient);
        $request->method('getFirstModel')->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;
        $payment->method('getCurrencyCode')->willReturn('EUR');

        $details = new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => null,
            'metadata' => ['refund' => ['test_refund', 'items' => ['test_item']]],
        ]);

        $request->method('getModel')->willReturn($details);
        $paymentEndpoint->method('get')->with(4)->willReturn($molliePayment);
        $this->convertOrderRefundData->method('convert')->willReturn(['5']);

        $molliePayment->method('hasRefunds')->willReturn(false);
        $molliePayment->id = '4';
        $molliePayment->amountRemaining = 5;
        $molliePayment->method('canBeRefunded')->willReturn(false);

        $this->loggerAction->expects($this->once())->method('addNegativeLog')->with('Payment 4 can not be refunded.');

        $this->expectException(UpdateHandlingException::class);
        $this->expectExceptionMessage('Payment 4 can not be refunded.');

        $this->refundAction->execute($request);
    }

    public function testItDoesNothingWhenMetadataHasNoRefund(): void
    {
        $request = $this->createMock(Refund::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $mollieApiClient->payments = $paymentEndpoint;

        $this->refundAction->setApi($mollieApiClient);

        $details = new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => null,
            'metadata' => ['not_refund' => ['test_refund']],
        ]);

        $request->method('getModel')->willReturn($details);

        $paymentEndpoint->expects(self::never())->method('get')->withAnyParameters();

        $this->refundAction->execute($request);
    }

    public function testItDoesNothingWhenRefundMetadataHasNoItems(): void
    {
        $request = $this->createMock(Refund::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $mollieApiClient->payments = $paymentEndpoint;

        $this->refundAction->setApi($mollieApiClient);

        $details = new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => null,
            'metadata' => ['refund' => ['test_refund']],
        ]);

        $request->method('getModel')->willReturn($details);

        $paymentEndpoint->expects(self::never())->method('get')->withAnyParameters();

        $this->refundAction->execute($request);
    }

    public function testItTriesToRefundAndThrowsApiException(): void
    {
        $request = $this->createMock(Refund::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $this->refundAction->setApi($mollieApiClient);
        $request->method('getFirstModel')->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;
        $payment->method('getCurrencyCode')->willReturn('EUR');

        $details = new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => null,
            'metadata' => ['refund' => ['test_refund', 'items' => ['test_item']]],
        ]);

        $request->method('getModel')->willReturn($details);
        $exception = new ApiException();
        $this->convertOrderRefundData->method('convert')->willReturn(['5']);

        $paymentEndpoint->method('get')->with(4)->willThrowException($exception);
        $this->loggerAction->expects($this->once())->method('addNegativeLog')->with($this->stringContains('API call failed:'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API call failed:');

        $this->refundAction->execute($request);
    }

    public function testItSupportsOnlyRefundRequestAndArrayAccess(): void
    {
        $request = $this->createMock(Refund::class);
        $arrayAccess = $this->createMock(\ArrayAccess::class);

        $request->method('getModel')->willReturn($arrayAccess);

        $this->assertTrue($this->refundAction->supports($request));
    }
}
