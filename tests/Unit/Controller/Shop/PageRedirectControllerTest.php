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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Controller\Shop\PageRedirectController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

final class PageRedirectControllerTest extends TestCase
{
    private const QR_ORDER_ID_SESSION_KEY = 'sylius_mollie_qr_order_id';

    private MockObject&RouterInterface $router;

    private MockObject&OrderRepositoryInterface $orderRepository;

    private MockObject&SessionInterface $session;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
    }

    public function testItThrowsNotFoundWhenOrderIdIsMissing(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->orderRepository->expects(self::never())->method('findOneBy');

        $controller = $this->createController();
        $controller->thankYouAction(new Request(), $this->session);
    }

    public function testItThrowsNotFoundWhenOrderIdIsEmpty(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->orderRepository->expects(self::never())->method('findOneBy');

        $controller = $this->createController();
        $controller->thankYouAction(new Request(['orderId' => '']), $this->session);
    }

    public function testItThrowsNotFoundWhenOrderIdDoesNotMatchSession(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(7);
        $this->orderRepository->expects(self::never())->method('findOneBy');

        $controller = $this->createController();
        $controller->thankYouAction(new Request(['orderId' => '42']), $this->session);
    }

    public function testItThrowsNotFoundWhenOrderIsUnknown(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(42);
        $this->orderRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['id' => '42'])
            ->willReturn(null);

        $controller = $this->createController();
        $controller->thankYouAction(new Request(['orderId' => '42']), $this->session);
    }

    public function testItThrowsNotFoundWhenOrderHasNoTokenValueAndPaymentNotCompleted(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $order = $this->createOrderMock(42, null, 'new');

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(42);
        $this->orderRepository->method('findOneBy')->with(['id' => '42'])->willReturn($order);

        $controller = $this->createController();
        $controller->thankYouAction(new Request(['orderId' => '42']), $this->session);
    }

    public function testItRedirectsToThankYouPageWhenPaymentIsCompleted(): void
    {
        $order = $this->createOrderMock(42, 'abc123', 'completed');

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(42);
        $this->orderRepository->method('findOneBy')->with(['id' => '42'])->willReturn($order);
        $this->session->expects(self::once())->method('set')->with('sylius_order_id', 42);
        $this->router->method('generate')->with('sylius_shop_order_thank_you')->willReturn('/en_US/order/thank-you');

        $controller = $this->createController();
        $response = $controller->thankYouAction(new Request(['orderId' => '42']), $this->session);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/en_US/order/thank-you', $response->getTargetUrl());
    }

    public function testItRedirectsToOrderShowWhenPaymentIsNotCompleted(): void
    {
        $order = $this->createOrderMock(42, 'abc123', 'new');

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(42);
        $this->orderRepository->method('findOneBy')->with(['id' => '42'])->willReturn($order);
        $this->session->expects(self::once())->method('set')->with('sylius_order_id', 42);
        $this->router->method('generate')->willReturnMap([
            ['sylius_shop_order_thank_you', [], 1, '/en_US/order/thank-you'],
            ['sylius_shop_order_show', ['tokenValue' => 'abc123'], 1, '/en_US/order/abc123'],
        ]);

        $controller = $this->createController();
        $response = $controller->thankYouAction(new Request(['orderId' => '42']), $this->session);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/en_US/order/abc123', $response->getTargetUrl());
    }

    public function testItSetsSessionOrderIdFromLoadedOrder(): void
    {
        $order = $this->createOrderMock(99, 'abc123', 'completed');

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(99);
        $this->orderRepository->method('findOneBy')->with(['id' => '99'])->willReturn($order);
        $this->router->method('generate')->willReturn('/en_US/order/thank-you');

        $this->session
            ->expects(self::once())
            ->method('set')
            ->with('sylius_order_id', 99);

        $controller = $this->createController();
        $controller->thankYouAction(new Request(['orderId' => '99']), $this->session);
    }

    private function createController(): PageRedirectController
    {
        return new PageRedirectController($this->router, $this->orderRepository);
    }

    private function createOrderMock(int $id, ?string $tokenValue, string $paymentState): MockObject&OrderInterface
    {
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getState')->willReturn($paymentState);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getId')->willReturn($id);
        $order->method('getTokenValue')->willReturn($tokenValue);
        $order->method('getLastPayment')->willReturn($payment);

        return $order;
    }
}
