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
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Controller\Shop\QrCodeAction;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class QrCodeActionTest extends TestCase
{
    private CartContextInterface&MockObject $cartContext;

    private MockObject&OrderRepositoryInterface $orderRepository;

    private QrCodeAction $action;

    protected function setUp(): void
    {
        $this->cartContext = $this->createMock(CartContextInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);

        $this->action = new QrCodeAction(
            $this->createMock(MollieLoggerActionInterface::class),
            $this->cartContext,
            $this->createMock(MollieApiClient::class),
            $this->createMock(MollieApiClientKeyResolverInterface::class),
            $this->orderRepository,
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(RepositoryInterface::class),
            $this->createMock(IntToStringConverterInterface::class),
        );
    }

    public function testItRemovesTheQrCodeFromTheOrderWithTheGivenToken(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $this->orderRepository->method('findOneByTokenValue')->with('TOKEN')->willReturn($order);

        $order->expects(self::once())->method('setQrCode')->with(null);
        $order->expects(self::once())->method('setMolliePaymentId')->with(null);
        $this->orderRepository->expects(self::once())->method('add')->with($order);

        $response = $this->action->removeQrCodeFromOrder(
            new Request(['orderToken' => 'TOKEN', 'shouldDeletePaymentId' => 'true']),
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenNoOrderMatchesTheGivenToken(): void
    {
        $this->cartContext->method('getCart')->willReturn($this->createMock(OrderInterface::class));
        $this->orderRepository->method('findOneByTokenValue')->with('TOKEN?utm_source=newsletter')->willReturn(null);

        $this->orderRepository->expects(self::never())->method('add');

        $response = $this->action->removeQrCodeFromOrder(
            new Request(['orderToken' => 'TOKEN?utm_source=newsletter', 'shouldDeletePaymentId' => 'true']),
        );

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
