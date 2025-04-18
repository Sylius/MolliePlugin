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

namespace Tests\Sylius\MolliePlugin\Unit\Checker\Refund;

use Mollie\Api\Resources\Order;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Refund\Checker\MollieOrderRefundChecker;
use Sylius\MolliePlugin\Refund\Checker\MollieOrderRefundCheckerInterface;

final class MollieOrderRefundCheckerTest extends TestCase
{
    private MollieOrderRefundCheckerInterface $mollieOrderRefundChecker;

    protected function setUp(): void
    {
        $this->mollieOrderRefundChecker = new MollieOrderRefundChecker();
    }

    public function testInitializable(): void
    {
        $this->assertInstanceOf(MollieOrderRefundChecker::class, $this->mollieOrderRefundChecker);
    }

    public function testReturnsTrueWithOrderLinesQuantityRefundedEqual1And1(): void
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->lines = [(object) ['quantityRefunded' => 1], (object) ['quantityRefunded' => 1]];

        $this->assertTrue($this->mollieOrderRefundChecker->check($orderMock));
    }

    public function testReturnsFalseWithOrderLinesQuantityRefundedEqual0(): void
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->lines = [(object) ['quantityRefunded' => 0], (object) ['quantityRefunded' => 0]];

        $this->assertFalse($this->mollieOrderRefundChecker->check($orderMock));
    }

    public function testReturnsTrueWithOrderLinesQuantityRefundedEqual1And0(): void
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->lines = [(object) ['quantityRefunded' => 1], (object) ['quantityRefunded' => 0]];

        $this->assertTrue($this->mollieOrderRefundChecker->check($orderMock));
    }

    public function testReturnsTrueWithOrderLinesQuantityRefundedEqual0And1(): void
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->lines = [(object) ['quantityRefunded' => 0], (object) ['quantityRefunded' => 1]];

        $this->assertTrue($this->mollieOrderRefundChecker->check($orderMock));
    }
}
