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

namespace Tests\Sylius\MolliePlugin\Unit\Controller\Shop;

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface as CorePaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Controller\Shop\PaymentWebhookController;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PaymentWebhookControllerTest extends TestCase
{
    private MockObject&MollieApiClient $mollieApiClient;

    private MockObject&MollieApiClientKeyResolverInterface $apiClientKeyResolver;

    private MockObject&OrderRepositoryInterface $orderRepository;

    private MockObject&PaymentRepositoryInterface $paymentRepository;

    private MockObject&MollieLoggerActionInterface $logger;

    private MockObject&StateMachineInterface $stateMachine;

    private EntityManagerInterface&MockObject $entityManager;

    private MockObject&PaymentEndpoint $paymentEndpoint;

    protected function setUp(): void
    {
        $this->mollieApiClient = $this->createMock(MollieApiClient::class);
        $this->apiClientKeyResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paymentRepository = $this->createMock(PaymentRepositoryInterface::class);
        $this->logger = $this->createMock(MollieLoggerActionInterface::class);
        $this->stateMachine = $this->createMock(StateMachineInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $this->mollieApiClient->payments = $this->paymentEndpoint;

        $this->mollieApiClient->method('getApiKey')->willReturn('test_key');
        $this->apiClientKeyResolver->method('getClientWithKey')->willReturn($this->mollieApiClient);
    }

    public function testItReturnsOkWhenOrderIsNotFound(): void
    {
        $controller = $this->createController();

        $request = new Request(['id' => 'tr_abc', 'orderId' => '42']);
        $this->paymentEndpoint->expects(self::once())->method('get')->willReturn($this->makeMolliePayment(PaymentStatus::STATUS_PAID));
        $this->orderRepository->expects(self::once())->method('findOneBy')->with(['id' => '42'])->willReturn(null);
        $this->stateMachine->expects(self::never())->method('apply');

        $response = $controller->__invoke($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testItReturnsOkWhenOrderHasNoPayment(): void
    {
        $controller = $this->createController();

        $request = new Request(['id' => 'tr_abc', 'orderId' => '42']);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getLastPayment')->willReturn(null);
        $this->paymentEndpoint->expects(self::once())->method('get')->willReturn($this->makeMolliePayment(PaymentStatus::STATUS_PAID));
        $this->orderRepository->method('findOneBy')->willReturn($order);
        $this->stateMachine->expects(self::never())->method('apply');

        $response = $controller->__invoke($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testItAppliesCompleteTransitionForPaidMolliePayment(): void
    {
        $controller = $this->createController();

        $request = new Request(['id' => 'tr_abc', 'orderId' => '42']);
        $payment = $this->createMock(CorePaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getLastPayment')->willReturn($payment);

        $this->paymentEndpoint->method('get')->willReturn($this->makeMolliePayment(PaymentStatus::STATUS_PAID));
        $this->orderRepository->method('findOneBy')->willReturn($order);

        $this->stateMachine->expects(self::once())
            ->method('can')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE)
            ->willReturn(true);
        $this->stateMachine->expects(self::once())
            ->method('apply')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE);
        $this->entityManager->expects(self::once())->method('flush');

        $response = $controller->__invoke($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testItSkipsTransitionWhenStateMachineRejectsIt(): void
    {
        $controller = $this->createController();

        $request = new Request(['id' => 'tr_abc', 'orderId' => '42']);
        $payment = $this->createMock(CorePaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $order->method('getLastPayment')->willReturn($payment);

        $this->paymentEndpoint->method('get')->willReturn($this->makeMolliePayment(PaymentStatus::STATUS_PAID));
        $this->orderRepository->method('findOneBy')->willReturn($order);

        $this->stateMachine->method('can')->willReturn(false);
        $this->stateMachine->expects(self::never())->method('apply');
        $this->entityManager->expects(self::never())->method('flush');

        $response = $controller->__invoke($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @group legacy
     */
    public function testItFallsBackToSetStateWhenStateMachineIsNotProvided(): void
    {
        $controller = new PaymentWebhookController(
            $this->mollieApiClient,
            $this->apiClientKeyResolver,
            $this->orderRepository,
            $this->paymentRepository,
            $this->logger,
        );

        $request = new Request(['id' => 'tr_abc', 'orderId' => '42']);
        $payment = $this->createMock(CorePaymentInterface::class);
        $payment->method('getState')->willReturn(PaymentInterface::STATE_NEW);
        $payment->expects(self::once())->method('setState')->with(PaymentInterface::STATE_COMPLETED);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getLastPayment')->willReturn($payment);

        $this->paymentEndpoint->method('get')->willReturn($this->makeMolliePayment(PaymentStatus::STATUS_PAID));
        $this->orderRepository->method('findOneBy')->willReturn($order);
        $this->paymentRepository->expects(self::once())->method('add')->with($payment);

        $response = $controller->__invoke($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    private function createController(): PaymentWebhookController
    {
        return new PaymentWebhookController(
            $this->mollieApiClient,
            $this->apiClientKeyResolver,
            $this->orderRepository,
            $this->paymentRepository,
            $this->logger,
            $this->stateMachine,
            $this->entityManager,
        );
    }

    private function makeMolliePayment(string $status): Payment
    {
        $payment = new Payment($this->mollieApiClient);
        $payment->id = 'tr_abc';
        $payment->status = $status;

        return $payment;
    }
}
