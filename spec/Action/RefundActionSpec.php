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

namespace spec\SyliusMolliePlugin\Action;

use SyliusMolliePlugin\Action\RefundAction;
use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\Helper\ConvertRefundDataInterface;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Refund;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;

final class RefundActionSpec extends ObjectBehavior
{
    public function let(MollieLoggerActionInterface $loggerAction, ConvertRefundDataInterface $convertOrderRefundData): void
    {
        $this->beConstructedWith(
            $loggerAction,
            $convertOrderRefundData
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RefundAction::class);
    }

    public function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    public function it_implements_api_aware_interface(): void
    {
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    public function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    public function it_returns_if_there_is_no_refund_array_key(
        Refund $request
    ): void {
        $details = new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => true,
            'metadata' => [],
        ]);

        $request->getModel()->willReturn($details);

        $this->execute($request);
    }

    public function it_refunds_action_when_payment_id_is_set(
        Refund $request,
        MollieApiClient $mollieApiClient,
        MollieLoggerActionInterface $loggerAction,
        PaymentInterface $payment,
        Payment $molliePayment,
        PaymentEndpoint $paymentEndpoint,
        ConvertRefundDataInterface $convertOrderRefundData
    ): void {
        $this->setApi($mollieApiClient);
        $request->getFirstModel()->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;
        $payment->getCurrencyCode()->willReturn('EUR');
        $request->getModel()->willReturn(new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => null,
            'metadata' => [
                'refund' => [
                    'test_refund',
                ],
            ],
        ]));

        $paymentEndpoint->get(4)->willReturn($molliePayment);
        $convertOrderRefundData->convert(['test_refund'], 'EUR')->willReturn(['5']);

        $molliePayment->hasRefunds()
            ->willReturn(false);

        $molliePayment->id = '4';
        $molliePayment->amountRemaining = 5;
        $molliePayment->canBeRefunded()->willReturn(true);

        $molliePayment->refund(['amount' => ['5']])->shouldBeCalled();
        $loggerAction->addLog(sprintf('Refund action with payment id %s', $molliePayment->id))->shouldBeCalled();

        $this->execute($request);
    }

    public function it_cannot_refund(
        Refund $request,
        MollieApiClient $mollieApiClient,
        MollieLoggerActionInterface $loggerAction,
        PaymentInterface $payment,
        Payment $molliePayment,
        PaymentEndpoint $paymentEndpoint,
        ConvertRefundDataInterface $convertOrderRefundData
    ): void {
        $this->setApi($mollieApiClient);
        $request->getFirstModel()->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;
        $payment->getCurrencyCode()->willReturn('EUR');
        $request->getModel()->willReturn(new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => null,
            'metadata' => [
                'refund' => [
                    'test_refund',
                ],
            ],
        ]));

        $paymentEndpoint->get(4)->willReturn($molliePayment);
        $convertOrderRefundData->convert(['test_refund'], 'EUR')->willReturn(['5']);

        $molliePayment->hasRefunds()
            ->willReturn(false);

        $molliePayment->id = '4';
        $molliePayment->amountRemaining = 5;
        $molliePayment->canBeRefunded()->willReturn(false);

        $loggerAction->addNegativeLog(sprintf('Payment %s can not be refunded.', $molliePayment->id))->shouldBeCalled();

        $this->shouldThrow(new UpdateHandlingException(sprintf('Payment %s can not be refunded.', $molliePayment->id)))
            ->during('execute', [$request]);
    }

    public function it_tries_to_refund_and_throws_api_exception(
        Refund $request,
        MollieApiClient $mollieApiClient,
        MollieLoggerActionInterface $loggerAction,
        PaymentInterface $payment,
        PaymentEndpoint $paymentEndpoint
    ): void {
        $this->setApi($mollieApiClient);
        $request->getFirstModel()->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;
        $payment->getCurrencyCode()->willReturn('EUR');

        $request->getModel()->willReturn(new ArrayObject([
            'payment_mollie_id' => 4,
            'created_in_mollie' => null,
            'metadata' => [
                'refund' => [
                    'test_refund',
                ],
            ],
        ]));
        $e = new ApiException();
        $paymentEndpoint->get(4)->willThrow($e);

        $loggerAction->addNegativeLog(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())))->shouldBeCalled();
        $this->shouldThrow(new \Exception(sprintf('API call failed: %s', htmlspecialchars($e->getMessage()))))
            ->during('execute', [$request]);
    }

    public function it_supports_only_refund_request_and_array_access(
        Refund $request,
        \ArrayAccess $arrayAccess
    ): void {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
