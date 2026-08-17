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
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Model\ApiType;
use Sylius\MolliePlugin\Payum\Action\CaptureAction;
use Sylius\MolliePlugin\Payum\Request\CreateCustomer;
use Sylius\MolliePlugin\Payum\Request\CreateOrder;
use Sylius\MolliePlugin\Payum\Request\CreatePayment;
use Sylius\MolliePlugin\Payum\Request\Subscription\CreateInternalRecurring;
use Sylius\MolliePlugin\Payum\Request\Subscription\CreateSubscriptionPayment;
use Sylius\MolliePlugin\Payum\Resolver\ExistingMollieSessionResolver;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;

final class CaptureActionTest extends TestCase
{
    private CaptureAction $captureAction;

    private GatewayInterface $gateway;

    private MollieApiClient $mollieApiClient;

    private GenericTokenFactory $genericTokenFactory;

    private Payum $payum;

    private OrderRepositoryInterface $orderRepository;

    private MollieApiClientKeyResolverInterface $apiClientKeyResolver;

    private PaymentRepositoryInterface $paymentRepository;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->apiClientKeyResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $this->paymentRepository = $this->createMock(PaymentRepositoryInterface::class);

        $this->captureAction = new CaptureAction(
            $this->orderRepository,
            $this->apiClientKeyResolver,
            $this->paymentRepository,
            new ExistingMollieSessionResolver(),
        );

        $this->gateway = $this->createMock(GatewayInterface::class);
        $this->mollieApiClient = $this->createMock(MollieApiClient::class);
        $this->genericTokenFactory = $this->createMock(GenericTokenFactory::class);
        $this->payum = $this->createMock(Payum::class);

        $this->captureAction->setGateway($this->gateway);
        $this->captureAction->setApi($this->mollieApiClient);
        $this->captureAction->setGenericTokenFactory($this->genericTokenFactory);
    }

    public function testItImplementsActionInterface(): void
    {
        $this->assertInstanceOf(ActionInterface::class, $this->captureAction);
    }

    public function testItImplementsGenericTokenFactoryAware(): void
    {
        $this->assertInstanceOf(GenericTokenFactoryAwareInterface::class, $this->captureAction);
    }

    public function testItImplementsApiAwareInterface(): void
    {
        $this->assertInstanceOf(ApiAwareInterface::class, $this->captureAction);
    }

    public function testItImplementsGatewayAwareInterface(): void
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, $this->captureAction);
    }

    public function testItExecutesWhenFactoryIsNull(): void
    {
        $request = $this->createMock(Capture::class);
        $details = new ArrayObject();
        $token = $this->createMock(TokenInterface::class);
        $identity = $this->createMock(IdentityInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);

        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $molliePayment = $this->createMock(\Mollie\Api\Resources\Payment::class);
        $paymentEndpoint->method('get')->willReturn($molliePayment);

        $this->mollieApiClient->payments = $paymentEndpoint;

        $this->mollieApiClient->method('isRecurringSubscription')->willReturn(true);

        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);
        $request->method('getToken')->willReturn($token);
        $token->method('getGatewayName')->willReturn('test');
        $token->method('getDetails')->willReturn($identity);
        $request->method('getModel')->willReturn($details);

        $this->captureAction->setGenericTokenFactory(null);

        $this->expectException(RuntimeException::class);

        $this->captureAction->execute($request);
    }

    public function testItExecutesWhenSubscriptionMollieIdIsTrue(): void
    {
        $request = $this->createMock(Capture::class);
        $details = new ArrayObject(['subscription_mollie_id' => true]);
        $payment = $this->createMock(PaymentInterface::class);
        $cancelToken = $this->createMock(TokenInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $molliePayment = $this->createMock(Payment::class);

        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $this->mollieApiClient->payments = $paymentEndpoint;

        $paymentEndpoint->method('get')->willReturn($molliePayment);

        $this->mollieApiClient->method('isRecurringSubscription')->willReturn(true);

        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);
        $request->method('getModel')->willReturn($details);
        $order->method('getQrCode')->willReturn('qr_code');
        $order->method('getMolliePaymentId')->willReturn('mollie_payment_id');

        $this->genericTokenFactory->method('createToken')
            ->willReturn($cancelToken)
        ;

        $this->gateway->expects($this->never())->method('execute')->with($this->isInstanceOf(CreateCustomer::class));
        $this->gateway->expects($this->never())->method('execute')->with($this->isInstanceOf(CreateInternalRecurring::class));
        $this->gateway->expects($this->never())->method('execute')->with($this->isInstanceOf(CreateSubscriptionPayment::class));

        $this->captureAction->execute($request);
    }

    public function testItExecutesWhenRecurringSubscriptionIsTrue(): void
    {
        $request = $this->createMock(Capture::class);
        $token = $this->createMock(TokenInterface::class);
        $notifyToken = $this->createMock(TokenInterface::class);
        $refundToken = $this->createMock(TokenInterface::class);
        $cancelToken = $this->createMock(TokenInterface::class);
        $identity = $this->createMock(IdentityInterface::class);
        $details = new ArrayObject([
            'sequenceType' => 'first',
            'metadata' => ['refund_token' => [
                'refund_token_hash',
            ],
                'cancel_token' => [
                    'cancel_hash',
                ],
                'methodType' => ApiType::ORDER_API,
                'order_id' => 'test_order_id',
            ],
            'webhookUrl' => 'url',
            'cancel_token' => 'cancel_hash',
            'backurl' => 'url',
        ]);
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $molliePayment = $this->createMock(\Mollie\Api\Resources\Payment::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $this->mollieApiClient->method('isRecurringSubscription')->willReturn(true);
        $this->mollieApiClient->payments = $paymentEndpoint;

        $paymentEndpoint->method('get')->willReturn($molliePayment);

        $request->method('getToken')->willReturn($token);
        $token->method('getGatewayName')->willReturn('test');
        $token->method('getDetails')->willReturn($identity);

        $this->genericTokenFactory->method('createNotifyToken')->willReturn($notifyToken);
        $notifyToken->method('getTargetUrl')->willReturn('url');
        $notifyToken->method('getHash')->willReturn('test');

        $this->genericTokenFactory->method('createRefundToken')->willReturn($refundToken);
        $refundToken->method('getHash')->willReturn('refund_token_hash');

        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);
        $request->method('getModel')->willReturn($details);

        $token->method('getTargetUrl')->willReturn('url');
        $token->method('getAfterUrl')->willReturn('url');
        $token->method('getHash')->willReturn('test');

        $request->method('getModel')->willReturn($details);

        $this->genericTokenFactory->method('createToken')->with('test', $identity, 'sylius_mollie_shop_cancel_subscription_mollie', ['orderId' => 'test_order_id'])->willReturn($cancelToken);

        $this->gateway->expects($this->exactly(3))->method('execute');

        $this->captureAction->execute($request);
    }

    public function testItExecutesWithMethodTypeEqualsOrderApi(): void
    {
        $request = $this->createMock(Capture::class);
        $payment = $this->createMock(PaymentInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $notifyToken = $this->createMock(TokenInterface::class);
        $refundToken = $this->createMock(TokenInterface::class);
        $identity = $this->createMock(IdentityInterface::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $order = $this->createMock(OrderInterface::class);

        $this->mollieApiClient->method('isRecurringSubscription')->willReturn(false);
        $this->mollieApiClient->payments = $paymentEndpoint;

        $request->method('getToken')->willReturn($token);
        $token->method('getGatewayName')->willReturn('test');
        $token->method('getDetails')->willReturn($identity);

        $this->genericTokenFactory->method('createNotifyToken')->willReturn($notifyToken);
        $notifyToken->method('getTargetUrl')->willReturn('url');
        $notifyToken->method('getHash')->willReturn('test');

        $this->genericTokenFactory->method('createRefundToken')->willReturn($refundToken);
        $refundToken->method('getHash')->willReturn('refund_token_hash');

        $details = new ArrayObject([
            'metadata' => [
                'refund_token' => ['refund_token_hash'],
                'methodType' => ApiType::ORDER_API,
                'molliePaymentMethods' => null,
            ],
            'methodType',
            'webhookUrl' => 'url',
            'backurl' => 'url',
        ]);
        $request->method('getModel')->willReturn($details);
        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);

        $token->method('getTargetUrl')->willReturn('url');
        $token->method('getAfterUrl')->willReturn('url');
        $token->method('getHash')->willReturn('test');

        $paymentEndpoint->method('create')->willReturn((object) ['id' => 1, 'getCheckoutUrl' => 'https://thisisnotanemptyurl.com']);

        $this->gateway->expects($this->atLeastOnce())->method('execute')->with($this->isInstanceOf(CreateOrder::class));

        $this->captureAction->execute($request);
    }

    public function testItExecutesWithMethodTypeEqualsPaymentApi(): void
    {
        $request = $this->createMock(Capture::class);
        $payment = $this->createMock(PaymentInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $notifyToken = $this->createMock(TokenInterface::class);
        $refundToken = $this->createMock(TokenInterface::class);
        $identity = $this->createMock(IdentityInterface::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $order = $this->createMock(OrderInterface::class);

        $this->mollieApiClient->method('isRecurringSubscription')->willReturn(false);
        $this->mollieApiClient->payments = $paymentEndpoint;

        $request->method('getToken')->willReturn($token);
        $token->method('getGatewayName')->willReturn('test');
        $token->method('getDetails')->willReturn($identity);

        $this->genericTokenFactory->method('createNotifyToken')->willReturn($notifyToken);
        $notifyToken->method('getTargetUrl')->willReturn('url');
        $notifyToken->method('getHash')->willReturn('test');

        $this->genericTokenFactory->method('createRefundToken')->willReturn($refundToken);
        $refundToken->method('getHash')->willReturn('refund_token_hash');

        $details = new ArrayObject([
            'metadata' => [
                'refund_token' => ['refund_token_hash'],
                'methodType' => ApiType::PAYMENT_API,
                'molliePaymentMethods' => 'not_klarna_scenario',
            ],
            'methodType',
            'webhookUrl' => 'url',
            'backurl' => 'url',
        ]);
        $request->method('getModel')->willReturn($details);
        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);

        $token->method('getTargetUrl')->willReturn('url');
        $token->method('getAfterUrl')->willReturn('url');
        $token->method('getHash')->willReturn('test');

        $paymentEndpoint->method('create')->willReturn((object) ['id' => 1, 'getCheckoutUrl' => 'https://thisisnotanemptyurl.com']);

        $this->gateway->expects($this->once())->method('execute')->with($this->isInstanceOf(CreatePayment::class));

        $this->captureAction->execute($request);
    }

    public function testItExecutesWithMethodTypeEqualsPaymentApiAndWithKlarnaPaymentMethod(): void
    {
        $request = $this->createMock(Capture::class);
        $payment = $this->createMock(PaymentInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $notifyToken = $this->createMock(TokenInterface::class);
        $refundToken = $this->createMock(TokenInterface::class);
        $identity = $this->createMock(IdentityInterface::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $order = $this->createMock(OrderInterface::class);

        $this->mollieApiClient->method('isRecurringSubscription')->willReturn(false);
        $this->mollieApiClient->payments = $paymentEndpoint;

        $request->method('getToken')->willReturn($token);
        $token->method('getGatewayName')->willReturn('test');
        $token->method('getDetails')->willReturn($identity);

        $this->genericTokenFactory->method('createNotifyToken')->willReturn($notifyToken);
        $notifyToken->method('getTargetUrl')->willReturn('url');
        $notifyToken->method('getHash')->willReturn('test');

        $this->genericTokenFactory->method('createRefundToken')->willReturn($refundToken);
        $refundToken->method('getHash')->willReturn('refund_token_hash');

        $details = new ArrayObject([
            'metadata' => [
                'refund_token' => ['refund_token_hash'],
                'methodType' => ApiType::PAYMENT_API,
                'molliePaymentMethods' => 'klarnapaynow',
            ],
            'methodType',
            'subscription_mollie_id',
            'payment_mollie_id',
            'order_mollie_id',
            'webhookUrl' => 'url',
            'backurl' => 'url',
        ]);
        $request->method('getModel')->willReturn($details);
        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);

        $token->method('getTargetUrl')->willReturn('url');
        $token->method('getAfterUrl')->willReturn('url');
        $token->method('getHash')->willReturn('test');

        $paymentEndpoint->method('create')->willReturn((object) ['id' => 1, 'getCheckoutUrl' => 'https://thisisnotanemptyurl.com']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method klarnapaynow is not allowed to use Payments API');

        $this->captureAction->execute($request);
    }

    public function testItReplacesAnOpenSessionThatCannotBeResumed(): void
    {
        $request = $this->createMock(Capture::class);
        $payment = $this->createMock(PaymentInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $notifyToken = $this->createMock(TokenInterface::class);
        $refundToken = $this->createMock(TokenInterface::class);
        $identity = $this->createMock(IdentityInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $openMolliePayment = new Payment($this->mollieApiClient);
        $openMolliePayment->id = 'tr_old';
        $openMolliePayment->status = PaymentStatus::STATUS_OPEN;
        $openMolliePayment->isCancelable = false;

        $this->mollieApiClient->method('isRecurringSubscription')->willReturn(false);
        $this->mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->with('tr_old')->willReturn($openMolliePayment);
        $paymentEndpoint->method('create')->willReturn((object) ['id' => 2, 'getCheckoutUrl' => 'https://thisisnotanemptyurl.com']);

        $details = new ArrayObject([
            'payment_mollie_id' => 'tr_old',
            'metadata' => [
                'refund_token' => ['refund_token_hash'],
                'methodType' => ApiType::PAYMENT_API,
                'molliePaymentMethods' => 'ideal',
            ],
            'webhookUrl' => 'url',
            'backurl' => 'url',
        ]);

        $request->method('getModel')->willReturn($details);
        $request->method('getFirstModel')->willReturn($payment);
        $request->method('getToken')->willReturn($token);
        $payment->method('getOrder')->willReturn($order);

        $token->method('getGatewayName')->willReturn('test');
        $token->method('getDetails')->willReturn($identity);
        $token->method('getTargetUrl')->willReturn('url');
        $token->method('getAfterUrl')->willReturn('url');
        $token->method('getHash')->willReturn('test');

        $this->genericTokenFactory->method('createNotifyToken')->willReturn($notifyToken);
        $notifyToken->method('getTargetUrl')->willReturn('url');
        $notifyToken->method('getHash')->willReturn('test');
        $this->genericTokenFactory->method('createRefundToken')->willReturn($refundToken);
        $refundToken->method('getHash')->willReturn('refund_token_hash');

        $createdPayment = false;
        $this->gateway->method('execute')->willReturnCallback(
            function ($executed) use (&$createdPayment): void {
                if ($executed instanceof CreatePayment) {
                    $createdPayment = true;
                }
            },
        );

        $this->captureAction->execute($request);

        self::assertTrue($createdPayment);
    }

    public function testItDoesNotCreateAnotherPaymentWhenTheBrowserReturnsFromMollie(): void
    {
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $openMolliePayment = new Payment($this->mollieApiClient);
        $openMolliePayment->id = 'tr_open';
        $openMolliePayment->status = PaymentStatus::STATUS_OPEN;
        $openMolliePayment->redirectUrl = 'https://shop.test/payment/capture/token-a';

        $this->mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->with('tr_open')->willReturn($openMolliePayment);
        $paymentEndpoint->expects($this->never())->method('create');
        $paymentEndpoint->expects($this->never())->method('cancel');
        $paymentEndpoint->expects($this->never())->method('update');

        $details = new ArrayObject(['payment_mollie_id' => 'tr_open']);
        $request = $this->createCaptureRequestFor($details, 'https://shop.test/payment/capture/token-a');

        $this->gateway->expects($this->never())->method('execute');

        $this->captureAction->execute($request);
    }

    public function testItResumesTheExistingSessionWhenTheMethodDidNotChange(): void
    {
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $openMolliePayment = new Payment($this->mollieApiClient);
        $openMolliePayment->id = 'tr_open';
        $openMolliePayment->status = PaymentStatus::STATUS_OPEN;
        $openMolliePayment->method = 'ideal';
        $openMolliePayment->redirectUrl = 'https://shop.test/payment/capture/spent-token';
        $openMolliePayment->_links = (object) [
            'checkout' => (object) ['href' => 'https://mollie.test/checkout/tr_open'],
        ];

        $this->mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->with('tr_open')->willReturn($openMolliePayment);
        $paymentEndpoint->expects($this->never())->method('create');
        $paymentEndpoint->expects($this->never())->method('cancel');
        $paymentEndpoint->expects($this->once())
            ->method('update')
            ->with('tr_open', ['redirectUrl' => 'https://shop.test/payment/capture/fresh-token'])
        ;

        $details = new ArrayObject([
            'payment_mollie_id' => 'tr_open',
            'molliePaymentMethods' => 'ideal',
        ]);
        $request = $this->createCaptureRequestFor($details, 'https://shop.test/payment/capture/fresh-token');

        $this->expectException(HttpRedirect::class);

        try {
            $this->captureAction->execute($request);
        } finally {
        }
    }

    public function testItResumesTheExistingSessionWhenMollieReportsNoMethod(): void
    {
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $openMolliePayment = new Payment($this->mollieApiClient);
        $openMolliePayment->id = 'tr_open';
        $openMolliePayment->status = PaymentStatus::STATUS_OPEN;
        $openMolliePayment->method = null;
        $openMolliePayment->redirectUrl = 'https://shop.test/payment/capture/spent-token';
        $openMolliePayment->_links = (object) [
            'checkout' => (object) ['href' => 'https://mollie.test/checkout/tr_open'],
        ];

        $this->mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->with('tr_open')->willReturn($openMolliePayment);
        $paymentEndpoint->expects($this->never())->method('create');
        $paymentEndpoint->expects($this->once())->method('update');

        $details = new ArrayObject([
            'payment_mollie_id' => 'tr_open',
            'molliePaymentMethods' => 'ideal',
        ]);
        $request = $this->createCaptureRequestFor($details, 'https://shop.test/payment/capture/fresh-token');

        $this->expectException(HttpRedirect::class);

        $this->captureAction->execute($request);
    }

    public function testItReplacesTheSessionWhenTheCustomerPickedAnotherMethod(): void
    {
        $notifyToken = $this->createMock(TokenInterface::class);
        $refundToken = $this->createMock(TokenInterface::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $openMolliePayment = new Payment($this->mollieApiClient);
        $openMolliePayment->id = 'tr_old';
        $openMolliePayment->status = PaymentStatus::STATUS_OPEN;
        $openMolliePayment->method = 'ideal';
        $openMolliePayment->isCancelable = false;
        $openMolliePayment->redirectUrl = 'https://shop.test/payment/capture/spent-token';
        $openMolliePayment->_links = (object) [
            'checkout' => (object) ['href' => 'https://mollie.test/checkout/tr_old'],
        ];

        $this->mollieApiClient->method('isRecurringSubscription')->willReturn(false);
        $this->mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->with('tr_old')->willReturn($openMolliePayment);
        $paymentEndpoint->expects($this->never())->method('update');

        $details = new ArrayObject([
            'payment_mollie_id' => 'tr_old',
            'molliePaymentMethods' => 'creditcard',
            'metadata' => [
                'methodType' => ApiType::PAYMENT_API,
                'molliePaymentMethods' => 'creditcard',
            ],
        ]);
        $request = $this->createCaptureRequestFor($details, 'https://shop.test/payment/capture/fresh-token');

        $this->genericTokenFactory->method('createNotifyToken')->willReturn($notifyToken);
        $notifyToken->method('getTargetUrl')->willReturn('url');
        $this->genericTokenFactory->method('createRefundToken')->willReturn($refundToken);
        $refundToken->method('getHash')->willReturn('refund_token_hash');

        $this->gateway->method('execute')->willReturnCallback(function ($executed): void {
            if ($executed instanceof Convert) {
                $executed->setResult([
                    'metadata' => [
                        'methodType' => ApiType::PAYMENT_API,
                        'molliePaymentMethods' => 'creditcard',
                    ],
                ]);
            }
        });

        $this->captureAction->execute($request);
    }

    private function createCaptureRequestFor(ArrayObject $details, string $tokenTargetUrl): Capture
    {
        $request = $this->createMock(Capture::class);
        $payment = $this->createMock(PaymentInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $identity = $this->createMock(IdentityInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $request->method('getModel')->willReturn($details);
        $request->method('getFirstModel')->willReturn($payment);
        $request->method('getToken')->willReturn($token);
        $payment->method('getOrder')->willReturn($order);

        $token->method('getGatewayName')->willReturn('test');
        $token->method('getDetails')->willReturn($identity);
        $token->method('getTargetUrl')->willReturn($tokenTargetUrl);

        return $request;
    }

    public function testItSupportsOnlyCaptureRequestAndArrayAccess(): void
    {
        $request = $this->createMock(Capture::class);
        $arrayAccess = $this->createMock(\ArrayAccess::class);

        $request->method('getModel')->willReturn($arrayAccess);

        $this->assertTrue($this->captureAction->supports($request));
    }
}
