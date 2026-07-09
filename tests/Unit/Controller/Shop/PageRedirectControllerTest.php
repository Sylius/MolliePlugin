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

        $this->router->method('generate')->willReturnCallback(
            static fn (string $name, array $parameters = []): string => match ($name) {
                'sylius_shop_order_thank_you' => '/en_US/order/thank-you',
                'sylius_shop_order_show' => '/en_US/order/' . ($parameters['tokenValue'] ?? ''),
                default => '/',
            },
        );
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

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(99);
        $this->orderRepository->expects(self::never())->method('findOneBy');

        $controller = $this->createController();
        $controller->thankYouAction(new Request(['orderId' => 42]), $this->session);
    }

    public function testItThrowsNotFoundWhenOrderDoesNotExist(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(42);
        $this->orderRepository->method('findOneBy')->with(['id' => 42])->willReturn(null);

        $controller = $this->createController();
        $controller->thankYouAction(new Request(['orderId' => 42]), $this->session);
    }

    public function testItRedirectsToThankYouPageWhenPaymentIsCompleted(): void
    {
        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(42);
        $this->session->expects(self::once())->method('set')->with('sylius_order_id', 42);

        $order = $this->createOrderMock(42, 'abc123', 'completed');
        $this->orderRepository->method('findOneBy')->with(['id' => 42])->willReturn($order);

        $controller = $this->createController();
        $response = $controller->thankYouAction(new Request(['orderId' => 42]), $this->session);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/en_US/order/thank-you', $response->getTargetUrl());
    }

    public function testItRedirectsToOrderShowWhenPaymentIsNotCompleted(): void
    {
        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(42);
        $this->session->expects(self::once())->method('set')->with('sylius_order_id', 42);

        $order = $this->createOrderMock(42, 'abc123', 'new');
        $this->orderRepository->method('findOneBy')->with(['id' => 42])->willReturn($order);

        $controller = $this->createController();
        $response = $controller->thankYouAction(new Request(['orderId' => 42]), $this->session);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/en_US/order/abc123', $response->getTargetUrl());
    }

    public function testItThrowsNotFoundWhenOrderHasNoTokenValue(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->session->method('get')->with(self::QR_ORDER_ID_SESSION_KEY)->willReturn(42);

        $order = $this->createOrderMock(42, null, 'new');
        $this->orderRepository->method('findOneBy')->with(['id' => 42])->willReturn($order);

        $controller = $this->createController();
        $controller->thankYouAction(new Request(['orderId' => 42]), $this->session);
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
