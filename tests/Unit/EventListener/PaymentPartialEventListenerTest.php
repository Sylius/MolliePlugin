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

namespace Tests\Sylius\MolliePlugin\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\EventListener\PaymentPartialEventListener;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefundInterface;
use Sylius\RefundPlugin\Entity\RefundInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;

final class PaymentPartialEventListenerTest extends TestCase
{
    public function testSkipsWhenRequestedIsNotGreaterThanAlreadyRefunded(): void
    {
        $orderPaymentRefund = $this->createMock(OrderPaymentRefundInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $refundRepository = $this->createMock(RepositoryInterface::class);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getId')->willReturn(1);
        $orderRepository->method('findOneBy')->with(['number' => '0001'])->willReturn($order);

        $existingRefund = $this->createMock(RefundInterface::class);
        $existingRefund->method('getAmount')->willReturn(1000);
        $refundRepository->method('findBy')->with(['order' => 1])->willReturn([$existingRefund]);

        $listener = new PaymentPartialEventListener($orderPaymentRefund, $logger, $orderRepository, $refundRepository);

        $event = new UnitsRefunded('0001', [], 1, 1000, 'USD', '');

        $orderPaymentRefund->expects(self::never())->method('refund');

        $listener($event);
    }

    public function testProcessesWhenRequestedIsGreaterThanAlreadyRefunded(): void
    {
        $orderPaymentRefund = $this->createMock(OrderPaymentRefundInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $refundRepository = $this->createMock(RepositoryInterface::class);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getId')->willReturn(1);
        $orderRepository->method('findOneBy')->with(['number' => '0001'])->willReturn($order);

        $existingRefund = $this->createMock(RefundInterface::class);
        $existingRefund->method('getAmount')->willReturn(500);
        $refundRepository->method('findBy')->with(['order' => 1])->willReturn([$existingRefund]);

        $listener = new PaymentPartialEventListener($orderPaymentRefund, $logger, $orderRepository, $refundRepository);

        $event = new UnitsRefunded('0001', [], 1, 1000, 'USD', '');

        $orderPaymentRefund->expects(self::once())->method('refund')->with($event);

        $listener($event);
    }
}
