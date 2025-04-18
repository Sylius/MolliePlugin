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

namespace Tests\Sylius\MolliePlugin\Unit\Order;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\MolliePlugin\Cloner\AdjustmentCloner;
use Sylius\MolliePlugin\Cloner\AdjustmentClonerInterface;

final class AdjustmentClonerTest extends TestCase
{
    private FactoryInterface $adjustmentFactoryMock;

    private AdjustmentInterface $adjustmentMock;

    private AdjustmentInterface $clonedAdjustmentMock;

    private AdjustmentCloner $adjustmentCloner;

    protected function setUp(): void
    {
        $this->adjustmentFactoryMock = $this->createMock(FactoryInterface::class);
        $this->adjustmentMock = $this->createMock(AdjustmentInterface::class);
        $this->clonedAdjustmentMock = $this->createMock(AdjustmentInterface::class);

        $this->adjustmentCloner = new AdjustmentCloner($this->adjustmentFactoryMock);

        $this->adjustmentMock->expects($this->any())->method('getAmount')->willReturn(5);
        $this->adjustmentMock->expects($this->any())->method('getType')->willReturn('test_type');
        $this->adjustmentMock->expects($this->any())->method('getDetails')->willReturn([
            'detail1',
            'detail2',
        ]);
        $this->adjustmentMock->expects($this->any())->method('getLabel')->willReturn('test_label');
        $this->adjustmentMock->expects($this->any())->method('isNeutral')->willReturn(true);

        $this->clonedAdjustmentMock->method('setAmount')->with(5);
        $this->clonedAdjustmentMock->method('setType')->with('test_type');
        $this->clonedAdjustmentMock->method('setDetails')->with([
            'detail1',
            'detail2',
        ]);
        $this->clonedAdjustmentMock->method('setLabel')->with('test_label');
        $this->clonedAdjustmentMock->method('setNeutral')->with(true);
    }

    public function testImplementsAdjustmentCloner(): void
    {
        $this->assertInstanceOf(AdjustmentClonerInterface::class, $this->adjustmentCloner);
    }

    public function testClonesAdjustmentWhenItIsLocked(): void
    {
        $this->adjustmentFactoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($this->clonedAdjustmentMock)
        ;

        $this->adjustmentMock->expects($this->once())->method('isLocked')->willReturn(true);
        $this->clonedAdjustmentMock->expects($this->once())->method('lock');

        $clonedAdjustment = $this->adjustmentCloner->clone($this->adjustmentMock);

        $this->assertNotSame($this->adjustmentMock, $clonedAdjustment);
        $this->assertInstanceOf(AdjustmentInterface::class, $clonedAdjustment);
    }

    public function testClonesAdjustmentWhenItIsUnlocked(): void
    {
        $this->adjustmentFactoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($this->clonedAdjustmentMock)
        ;

        $this->adjustmentMock->expects($this->once())->method('isLocked')->willReturn(false);
        $this->clonedAdjustmentMock->expects($this->once())->method('unlock');

        $clonedAdjustment = $this->adjustmentCloner->clone($this->adjustmentMock);

        $this->assertNotSame($this->adjustmentMock, $clonedAdjustment);
        $this->assertInstanceOf(AdjustmentInterface::class, $clonedAdjustment);
    }
}
