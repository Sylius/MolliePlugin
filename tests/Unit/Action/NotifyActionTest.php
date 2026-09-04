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
use Mollie\Api\Types\PaymentStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Action\NotifyAction;
use Sylius\MolliePlugin\Payum\Request\Subscription\StatusRecurringSubscription;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicatorInterface;
use Symfony\Component\HttpFoundation\Response;

final class NotifyActionTest extends TestCase
{
    private NotifyAction $notifyAction;

    private GetHttpRequest $getHttpRequest;

    private MollieSubscriptionRepositoryInterface $subscriptionRepository;

    private MollieOrderStatesApplicatorInterface $setStatusOrderAction;

    private MollieLoggerActionInterface $loggerAction;

    protected function setUp(): void
    {
        $this->getHttpRequest = $this->createMock(GetHttpRequest::class);
        $this->subscriptionRepository = $this->createMock(MollieSubscriptionRepositoryInterface::class);
        $this->setStatusOrderAction = $this->createMock(MollieOrderStatesApplicatorInterface::class);
        $this->loggerAction = $this->createMock(MollieLoggerActionInterface::class);

        $this->notifyAction = new NotifyAction(
            $this->getHttpRequest,
            $this->subscriptionRepository,
            $this->setStatusOrderAction,
            $this->loggerAction,
        );
    }

    public function testItImplementsActionInterface(): void
    {
        $this->assertInstanceOf(ActionInterface::class, $this->notifyAction);
    }

    public function testItImplementsApiAwareInterface(): void
    {
        $this->assertInstanceOf(ApiAwareInterface::class, $this->notifyAction);
    }

    public function testItImplementsGatewayAwareInterface(): void
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, $this->notifyAction);
    }

    public function testItExecutesWhenSequenceTypeIsSet(): void
    {
        $request = $this->createMock(Notify::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $payment = new Payment($mollieApiClient);
        $payment->id = 'payment_id';
        $payment->metadata = (object) ['order_id' => 15];
        $subscription = $this->createMock(MollieSubscriptionInterface::class);

        $this->notifyAction->setGateway($gateway);
        $this->notifyAction->setApi($mollieApiClient);

        $this->getHttpRequest->request = ['id' => 'payment_id'];
        $request->method('getModel')->willReturn(new \ArrayObject([
            'sequenceType' => 'test',
            'metadata' => ['order_id' => 15],
            'payment_mollie_id' => 'payment_id',
        ]));

        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->willReturn($payment);

        $this->subscriptionRepository->method('findByOrderId')->willReturn([$subscription]);

        $gateway->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->getHttpRequest],
                [$this->callback(function ($request) use ($subscription) {
                    return $request instanceof StatusRecurringSubscription &&
                        $request->getFirstModel() === $subscription &&
                        $request->getPaymentId() === 'payment_id';
                })],
            )
        ;

        $this->loggerAction->expects($this->once())
            ->method('addLog')
            ->with('Notify payment with id: payment_id')
        ;

        try {
            $this->notifyAction->execute($request);
            $this->fail('Expected HttpResponse exception was not thrown.');
        } catch (HttpResponse $exception) {
            $this->assertSame(Response::HTTP_OK, $exception->getStatusCode());
        }
    }

    public function testItThrowsApiExceptionWhenErrorOccurs(): void
    {
        $request = $this->createMock(Notify::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $this->notifyAction->setGateway($gateway);
        $this->notifyAction->setApi($mollieApiClient);

        $this->getHttpRequest->request = ['id' => 1];
        $request->method('getModel')->willReturn(new \ArrayObject(['sequenceType' => 'test']));

        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->willThrowException(new ApiException('test_error'));

        $this->loggerAction->expects($this->once())
            ->method('addNegativeLog')
            ->with($this->matchesRegularExpression('/Error with get customer from mollie with: .*test_error/'))
        ;

        $this->expectException(ApiException::class);

        $this->notifyAction->execute($request);
    }

    public function testItIgnoresOrphanPaidPaymentWithoutAdoptingIt(): void
    {
        $request = $this->createMock(Notify::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $incoming = new Payment($mollieApiClient);
        $incoming->id = 'tr_incoming';
        $incoming->status = PaymentStatus::STATUS_PAID;
        $incoming->metadata = (object) ['order_id' => 15];

        $this->notifyAction->setGateway($gateway);
        $this->notifyAction->setApi($mollieApiClient);

        $this->getHttpRequest->request = ['id' => 'tr_incoming'];
        $model = new \ArrayObject([
            'metadata' => ['order_id' => 15],
            'payment_mollie_id' => 'tr_current',
        ]);
        $request->method('getModel')->willReturn($model);

        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->willReturn($incoming);

        $this->loggerAction->expects($this->once())
            ->method('addNegativeLog')
            ->with($this->matchesRegularExpression(
                '/Mollie payment tr_incoming .* was paid but the order was not credited from it/',
            ))
        ;
        $this->loggerAction->expects($this->never())->method('addLog');

        $this->notifyAction->execute($request);
    }

    public function testItDoesNotTreatOrderApiPaymentWebhookAsOrphan(): void
    {
        $request = $this->createMock(Notify::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $this->notifyAction->setGateway($gateway);
        $this->notifyAction->setApi($mollieApiClient);

        $this->getHttpRequest->request = ['id' => 'tr_inside_order'];
        $model = new \ArrayObject([
            'metadata' => ['order_id' => 15],
            'order_mollie_id' => 'ord_current',
        ]);
        $request->method('getModel')->willReturn($model);

        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->expects($this->never())->method('get');

        $this->loggerAction->expects($this->never())->method('addLog');
        $this->loggerAction->expects($this->never())->method('addNegativeLog');

        $this->notifyAction->execute($request);
    }

    public function testItDoesNothingWhenOrphanIsNotPaid(): void
    {
        $request = $this->createMock(Notify::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $incoming = new Payment($mollieApiClient);
        $incoming->id = 'tr_incoming';
        $incoming->status = PaymentStatus::STATUS_OPEN;
        $incoming->metadata = (object) ['order_id' => 15];

        $this->notifyAction->setGateway($gateway);
        $this->notifyAction->setApi($mollieApiClient);

        $this->getHttpRequest->request = ['id' => 'tr_incoming'];
        $model = new \ArrayObject([
            'metadata' => ['order_id' => 15],
            'payment_mollie_id' => 'tr_current',
        ]);
        $request->method('getModel')->willReturn($model);

        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->willReturn($incoming);

        $this->loggerAction->expects($this->never())->method('addLog');

        $this->notifyAction->execute($request);
    }

    public function testItDoesNothingWhenIncomingIdMatchesCurrent(): void
    {
        $request = $this->createMock(Notify::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);

        $this->notifyAction->setGateway($gateway);
        $this->notifyAction->setApi($mollieApiClient);

        $this->getHttpRequest->request = ['id' => 'tr_current'];
        $model = new \ArrayObject([
            'metadata' => ['order_id' => 15],
            'payment_mollie_id' => 'tr_current',
        ]);
        $request->method('getModel')->willReturn($model);

        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->expects($this->never())->method('get');

        $this->loggerAction->expects($this->never())->method('addLog');

        $this->notifyAction->execute($request);
    }

    public function testItDoesNothingWhenOrderIdDoesNotMatch(): void
    {
        $request = $this->createMock(Notify::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $incoming = new Payment($mollieApiClient);
        $incoming->id = 'tr_incoming';
        $incoming->status = PaymentStatus::STATUS_PAID;
        $incoming->metadata = (object) ['order_id' => 99];

        $this->notifyAction->setGateway($gateway);
        $this->notifyAction->setApi($mollieApiClient);

        $this->getHttpRequest->request = ['id' => 'tr_incoming'];
        $model = new \ArrayObject([
            'metadata' => ['order_id' => 15],
            'payment_mollie_id' => 'tr_current',
        ]);
        $request->method('getModel')->willReturn($model);

        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->method('get')->willReturn($incoming);

        $this->loggerAction->expects($this->never())->method('addLog');

        $this->notifyAction->execute($request);
    }
}
