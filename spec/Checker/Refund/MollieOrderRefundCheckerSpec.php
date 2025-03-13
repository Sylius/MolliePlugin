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

namespace spec\SyliusMolliePlugin\Checker\Refund;

use SyliusMolliePlugin\Checker\Refund\MollieOrderRefundChecker;
use SyliusMolliePlugin\Checker\Refund\MollieOrderRefundCheckerInterface;
use Mollie\Api\Resources\Order;
use PhpSpec\ObjectBehavior;

final class MollieOrderRefundCheckerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieOrderRefundChecker::class);
        $this->shouldImplement(MollieOrderRefundCheckerInterface::class);
    }

    public function it_returns_true_with_order_lines_quantity_refunded_equal_1_and_1(
        Order $order
    ): void {
        $line = (object) [
            'quantityRefunded' => 1,
        ];
        $line2 = (object) [
            'quantityRefunded' => 1,
        ];
        $lines = (object) [
            $line,
            $line2,
        ];

        $order->lines = $lines;

        $this->check($order)
            ->shouldReturn(true);
    }

    public function it_returns_false_with_order_lines_quantity_refunded_equal_0(
        Order $order
    ): void {
        $line = (object) [
            'quantityRefunded' => 0,
        ];
        $line2 = (object) [
            'quantityRefunded' => 0,
        ];
        $lines = (object) [
            $line,
            $line2,
        ];

        $order->lines = $lines;

        $this->check($order)
            ->shouldReturn(false);
    }

    public function it_returns_true_with_order_lines_quantity_refunded_equal_1_and_0(
        Order $order
    ): void {
        $line = (object) [
            'quantityRefunded' => 1,
        ];
        $line2 = (object) [
            'quantityRefunded' => 0,
        ];
        $lines = (object) [
            $line,
            $line2,
        ];

        $order->lines = $lines;

        $this->check($order)
            ->shouldReturn(true);
    }

    public function it_returns_true_with_order_lines_quantity_refunded_equal_0_and_1(
        Order $order
    ): void {
        $line = (object) [
            'quantityRefunded' => 0,
        ];
        $line2 = (object) [
            'quantityRefunded' => 1,
        ];
        $lines = (object) [
            $line,
            $line2,
        ];

        $order->lines = $lines;

        $this->check($order)
            ->shouldReturn(true);
    }
}
