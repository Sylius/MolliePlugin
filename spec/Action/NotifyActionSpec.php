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

use SyliusMolliePlugin\Action\NotifyAction;
use SyliusMolliePlugin\Action\StateMachine\SetStatusOrderActionInterface;
use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use SyliusMolliePlugin\Request\StateMachine\StatusRecurringSubscription;
use Mollie\Api\Endpoints\OrderEndpoint;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order;
use Mollie\Api\Resources\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

final class NotifyActionSpec extends ObjectBehavior
{
    public function let(
        GetHttpRequest $getHttpRequest,
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        SetStatusOrderActionInterface $setStatusOrderAction,
        MollieLoggerActionInterface $loggerAction
    ): void {
        $this->beConstructedWith(
            $getHttpRequest,
            $subscriptionRepository,
            $setStatusOrderAction,
            $loggerAction
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(NotifyAction::class);
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

    public function it_executes_when_sequence_type_is_set(
        Notify $request,
        GatewayInterface $gateway,
        GetHttpRequest $getHttpRequest,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        MollieSubscriptionInterface $subscription,
        MollieLoggerActionInterface $loggerAction
    ): void {
        $this->setGateway($gateway);
        $this->setApi($mollieApiClient);
        $getHttpRequest->request = ['id' => 'payment_id'];

        $request->getModel()->willReturn(new \ArrayObject([
            'sequenceType' => 'test',
            'metadata' => [
                'order_id' => 15,
            ],
            'payment_mollie_id' => 'payment_id',
        ]));
        $mollieApiClient->payments = $paymentEndpoint;

        $paymentEndpoint->get('payment_id')->willReturn($payment);
        $payment->id = 'payment_id';
        $payment->metadata = new \stdClass();
        $payment->metadata->order_id = 15;
        $subscriptionRepository->findByOrderId(15)->willReturn([$subscription]);

        $gateway->execute($getHttpRequest)->shouldBeCalled();
        $gateway->execute(new StatusRecurringSubscription($subscription->getWrappedObject(), $payment->id))->shouldBeCalled();
        $loggerAction->addLog(sprintf('Notify payment with id: %s', $payment->id))->shouldBeCalled();

        $this->shouldThrow(new HttpResponse(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK))
            ->during('execute', [$request]);
    }

    public function it_executes_when_sequence_type_is_set_and_throw_api_exception(
        Notify $request,
        GatewayInterface $gateway,
        GetHttpRequest $getHttpRequest,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        MollieLoggerActionInterface $loggerAction
    ): void {
        $this->setGateway($gateway);
        $this->setApi($mollieApiClient);
        $getHttpRequest->request = ['id' => 1];
        $request->getModel()->willReturn(new \ArrayObject([
            'sequenceType' => 'test',
        ]));
        $mollieApiClient->payments = $paymentEndpoint;

        $e = new \Exception('test_error');
        $paymentEndpoint->get(1)->willThrow($e);

        $loggerAction->addNegativeLog(
            sprintf('Error with get customer from mollie with: %s', 'test_error')
        )->shouldBeCalled();
        $this->shouldThrow(ApiException::class)->during('execute', [$request]);
    }

    public function it_executes_when_order_mollie_id_is_set(
        Notify $request,
        GatewayInterface $gateway,
        GetHttpRequest $getHttpRequest,
        MollieApiClient $mollieApiClient,
        Order $order,
        OrderEndpoint $orderEndpoint,
        MollieLoggerActionInterface $loggerAction,
        SetStatusOrderActionInterface $setStatusOrderAction
    ): void {
        $this->setGateway($gateway);
        $this->setApi($mollieApiClient);
        $getHttpRequest->request = ['id' => 'ord_test_order_id'];
        $request->getModel()->willReturn(new \ArrayObject([
            'order_mollie_id' => 'ord_test_order_id',
            'metadata' => [
                'order_id' => 42,
            ],
        ]));
        $mollieApiClient->orders = $orderEndpoint;
        $orderEndpoint->get('ord_test_order_id')->willReturn($order);
        $order->id = 42;
        $order->metadata = new \stdClass();
        $order->metadata->order_id = 42;

        $gateway->execute($getHttpRequest)->shouldBeCalled();
        $setStatusOrderAction->execute($order)->shouldBeCalled();
        $loggerAction->addLog(sprintf('Notify order with id: %s', $order->id))->shouldBeCalled();

        $this->shouldThrow(new HttpResponse(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK))
            ->during('execute', [$request]);
    }

    public function it_executes_with_order_mollie_id_is_set_and_throws_api_exception(
        Notify $request,
        GatewayInterface $gateway,
        GetHttpRequest $getHttpRequest,
        MollieApiClient $mollieApiClient,
        OrderEndpoint $orderEndpoint,
        MollieLoggerActionInterface $loggerAction
    ): void {
        $this->setGateway($gateway);
        $this->setApi($mollieApiClient);
        $getHttpRequest->request = ['id' => 'ord_test_order_id'];
        $request->getModel()->willReturn(new \ArrayObject([
            'order_mollie_id' => 'ord_test_order_id',
        ]));
        $mollieApiClient->orders = $orderEndpoint;

        $e = new \Exception('test_error');
        $orderEndpoint->get('ord_test_order_id')->willThrow($e);

        $loggerAction->addNegativeLog(
            sprintf('Error with get order from mollie with: %s', 'test_error')
        )->shouldBeCalled();

        $this->shouldThrow(ApiException::class)->during('execute', [$request]);
    }

    public function it_supports_only_notify_request_and_array_access(
        Notify $request,
        \ArrayAccess $arrayAccess
    ): void {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
