<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Action;

use Sylius\MolliePlugin\Action\CaptureAction;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\MolliePlugin\Request\Api\CreateCustomer;
use Sylius\MolliePlugin\Request\Api\CreateInternalRecurring;
use Sylius\MolliePlugin\Request\Api\CreateOnDemandSubscription;
use Sylius\MolliePlugin\Request\Api\CreateOrder;
use Sylius\MolliePlugin\Request\Api\CreatePayment;
use Sylius\MolliePlugin\Request\Api\CreateSubscriptionPayment;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\InvalidArgumentException;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureActionSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(CaptureAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_generic_token_factory_aware(): void
    {
        $this->shouldHaveType(GenericTokenFactoryAwareInterface::class);
    }

    function it_implements_api_aware_interface(): void
    {
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    function it_executes_when_factory_is_null(
        Capture $request,
        ArrayObject $details,
        TokenInterface $token,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        IdentityInterface $identity
    ): void {
        $this->setGateway($gateway);
        $mollieApiClient->isRecurringSubscription()->willReturn(true);
        $this->setApi($mollieApiClient);
        $this->setGenericTokenFactory();

        $request->getToken()->willReturn($token);
        $token->getGatewayName()->willReturn('test');
        $token->getDetails()->willReturn($identity);
        $request->getModel()->willReturn($details);

        $this->shouldThrow( new RuntimeException())->during('execute',[$request]);
    }

    function it_executes_when_subscription_mollie_id_is_true(
        Capture $request,
        ArrayObject $details,
        PaymentInterface $payment,
        TokenInterface $cancelToken,
        GenericTokenFactory $genericTokenFactory,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        IdentityInterface $identity
    ): void {
        $this->setGateway($gateway);
        $mollieApiClient->isRecurringSubscription()->willReturn(true);
        $this->setApi($mollieApiClient);

        $request->getFirstModel()->willReturn($payment);
        $request->getModel()->willReturn(new ArrayObject([
            'subscription_mollie_id' => true
        ]));
        $genericTokenFactory->createToken(
            'test',
            $identity,
            'sylius_mollie_plugin_cancel_subscription_mollie',
            ['orderId' => 'test_order_id']
        )->willReturn($cancelToken);

        $gateway->execute(new CreateCustomer($details->getWrappedObject()))->shouldNotBeCalled();
        $gateway->execute(new CreateInternalRecurring($details->getWrappedObject()))->shouldNotBeCalled();
        $gateway->execute(new CreateSubscriptionPayment($details->getWrappedObject()))->shouldNotBeCalled();

        $this->execute($request);
    }

    function it_executes_when_recurring_subscription_is_true(
        Capture $request,
        PaymentInterface $payment,
        TokenInterface $token,
        TokenInterface $notifyToken,
        TokenInterface $refundToken,
        TokenInterface $cancelToken,
        Payum $payum,
        GenericTokenFactory $genericTokenFactory,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        IdentityInterface $identity
    ): void {
        $this->setGateway($gateway);
        $mollieApiClient->isRecurringSubscription()->willReturn(true);
        $this->setApi($mollieApiClient);
        $request->getToken()->willReturn($token);
        $token->getGatewayName()->willReturn('test');
        $token->getDetails()->willReturn($identity);

        $genericTokenFactory->createNotifyToken('test', $identity)->willReturn($notifyToken);
        $notifyToken->getTargetUrl()->willReturn('url');
        $notifyToken->getHash()->willReturn('test');

        $genericTokenFactory->createRefundToken('test', $identity)->willReturn($refundToken);
        $refundToken->getHash()->willReturn('refund_token_hash');

        $this->setGenericTokenFactory($genericTokenFactory);
        $payum->getTokenFactory()->willReturn($genericTokenFactory);

        $request->getFirstModel()->willReturn($payment);

        $token->getTargetUrl()->willReturn('url');
        $token->getAfterUrl()->willReturn('url');
        $token->getHash()->willReturn('test');

        $details = new ArrayObject([
            'sequenceType' => 'first',
            'metadata' => ['refund_token' => [
                'refund_token_hash'
            ],
                'cancel_token' => [
                    'cancel_hash'
                ],
                'methodType' => Options::ORDER_API,
                'order_id' => 'test_order_id',
            ],
            'webhookUrl' => 'url',
            'cancel_token' => 'cancel_hash',
            'backurl' => 'url',
        ]);
        $request->getModel()->willReturn($details);

        $genericTokenFactory->createToken(
            'test',
            $identity,
            'sylius_mollie_plugin_cancel_subscription_mollie',
            ['orderId' => 'test_order_id']
        )->willReturn($cancelToken);

        $gateway->execute(new CreateCustomer($details))->shouldBeCalled();
        $gateway->execute(new CreateInternalRecurring($details))->shouldBeCalled();
        $gateway->execute(new CreateOnDemandSubscription($details))->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_with_method_type_equals_order_api(
        Capture $request,
        PaymentInterface $payment,
        TokenInterface $token,
        TokenInterface $notifyToken,
        TokenInterface $refundToken,
        Payum $payum,
        GenericTokenFactory $genericTokenFactory,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        IdentityInterface $identity
    ): void {
        $this->setGateway($gateway);
        $mollieApiClient->isRecurringSubscription()->willReturn(false);
        $this->setApi($mollieApiClient);
        $request->getToken()->willReturn($token);
        $token->getGatewayName()->willReturn('test');
        $token->getDetails()->willReturn($identity);

        $genericTokenFactory->createNotifyToken('test', $identity)->willReturn($notifyToken);
        $notifyToken->getTargetUrl()->willReturn('url');
        $notifyToken->getHash()->willReturn('test');

        $genericTokenFactory->createRefundToken('test', $identity)->willReturn($refundToken);
        $refundToken->getHash()->willReturn('refund_token_hash');

        $this->setGenericTokenFactory($genericTokenFactory);
        $payum->getTokenFactory()->willReturn($genericTokenFactory);

        $details = new ArrayObject([
            'metadata' => [
                'refund_token' => [
                    'refund_token_hash'
                ],
                'methodType' => Options::ORDER_API
            ],
            'methodType',
            'subscription_mollie_id',
            'payment_mollie_id',
            'order_mollie_id',
            'webhookUrl' => 'url',
            'backurl' => 'url',
        ]);

        $request->getModel()->willReturn($details);
        $request->getFirstModel()->willReturn($payment);

        $token->getTargetUrl()->willReturn('url');
        $token->getAfterUrl()->willReturn('url');
        $token->getHash()->willReturn('test');

        $payment = \Mockery::mock('payment');
        $payment->id = 1;
        $payment->shouldReceive('getCheckoutUrl')->andReturn('https://thisisnotanemptyurl.com');
        $paymentEndpoint->create([
            'amount' => null,
            'description' => null,
            'redirectUrl' => 'url',
            'webhookUrl' => null,
            'metadata' => null,
        ])->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;

        $gateway->execute(new CreateOrder($details))->shouldBeCalledOnce();

        $this->execute($request);
    }

    function it_executes_with_method_type_equals_payment_api(
        Capture $request,
        PaymentInterface $payment,
        TokenInterface $token,
        TokenInterface $notifyToken,
        TokenInterface $refundToken,
        Payum $payum,
        GenericTokenFactory $genericTokenFactory,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        IdentityInterface $identity
    ): void {
        $this->setGateway($gateway);
        $mollieApiClient->isRecurringSubscription()->willReturn(false);
        $this->setApi($mollieApiClient);
        $request->getToken()->willReturn($token);
        $token->getGatewayName()->willReturn('test');
        $token->getDetails()->willReturn($identity);

        $genericTokenFactory->createNotifyToken('test', $identity)->willReturn($notifyToken);
        $notifyToken->getTargetUrl()->willReturn('url');
        $notifyToken->getHash()->willReturn('test');

        $genericTokenFactory->createRefundToken('test', $identity)->willReturn($refundToken);
        $refundToken->getHash()->willReturn('refund_token_hash');

        $this->setGenericTokenFactory($genericTokenFactory);
        $payum->getTokenFactory()->willReturn($genericTokenFactory);

        $request->getFirstModel()->willReturn($payment);

        $details = new ArrayObject([
            'metadata' => [
                'refund_token' => [
                    'refund_token_hash'
                ],
                'methodType' => Options::PAYMENT_API,
                'molliePaymentMethods' => 'not_klarna_scenario',
            ],
            'methodType',
            'subscription_mollie_id',
            'payment_mollie_id',
            'order_mollie_id',
            'webhookUrl' => 'url',
            'backurl' => 'url',
        ]);
        $request->getModel()->willReturn($details);
        $token->getTargetUrl()->willReturn('url');
        $token->getAfterUrl()->willReturn('url');
        $token->getHash()->willReturn('test');

        $payment = \Mockery::mock('payment');
        $payment->id = 1;
        $payment->shouldReceive('getCheckoutUrl')->andReturn('https://thisisnotanemptyurl.com');
        $paymentEndpoint->create([
            'amount' => null,
            'description' => null,
            'redirectUrl' => 'url',
            'webhookUrl' => null,
            'metadata' => null,
        ])->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;

        $gateway->execute(new CreatePayment($details))->shouldBeCalledOnce();

        $this->execute($request);
    }

    function it_executes_with_method_type_equals_payment_api_and_with_klarna_payment_method(
        Capture $request,
        PaymentInterface $payment,
        TokenInterface $token,
        TokenInterface $notifyToken,
        TokenInterface $refundToken,
        Payum $payum,
        GenericTokenFactory $genericTokenFactory,
        GatewayInterface $gateway,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        IdentityInterface $identity
    ): void {
        $this->setGateway($gateway);
        $mollieApiClient->isRecurringSubscription()->willReturn(false);
        $this->setApi($mollieApiClient);
        $request->getToken()->willReturn($token);
        $token->getGatewayName()->willReturn('test');
        $token->getDetails()->willReturn($identity);

        $genericTokenFactory->createNotifyToken('test', $identity)->willReturn($notifyToken);
        $notifyToken->getTargetUrl()->willReturn('url');
        $notifyToken->getHash()->willReturn('test');

        $genericTokenFactory->createRefundToken('test', $identity)->willReturn($refundToken);
        $refundToken->getHash()->willReturn('refund_token_hash');

        $this->setGenericTokenFactory($genericTokenFactory);
        $payum->getTokenFactory()->willReturn($genericTokenFactory);

        $details = new ArrayObject([
            'metadata' => [
                'refund_token' => [
                    'refund_token_hash'
                ],
                'methodType' => Options::PAYMENT_API,
                'molliePaymentMethods' => 'klarnapaynow',
            ],
            'methodType',
            'subscription_mollie_id',
            'payment_mollie_id',
            'order_mollie_id',
            'webhookUrl' => 'url',
            'backurl' => 'url',
       ]);
        $request->getModel()->willReturn($details);
        $request->getFirstModel()->willReturn($payment);

        $token->getTargetUrl()->willReturn('url');
        $token->getAfterUrl()->willReturn('url');
        $token->getHash()->willReturn('test');

        $payment = \Mockery::mock('payment');
        $payment->id = 1;
        $payment->shouldReceive('getCheckoutUrl')->andReturn('https://thisisnotanemptyurl.com');
        $paymentEndpoint->create([
            'amount' => null,
            'description' => null,
            'redirectUrl' => 'url',
            'webhookUrl' => null,
            'metadata' => null,
        ])->willReturn($payment);
        $mollieApiClient->payments = $paymentEndpoint;

        $this->shouldThrow(new InvalidArgumentException('Method klarnapaynow is not allowed to use Payments API'))
            ->during('execute',[$request]);
    }

    function it_supports_only_capture_request_and_array_access(
        Capture $request,
        \ArrayAccess $arrayAccess
    ): void {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
