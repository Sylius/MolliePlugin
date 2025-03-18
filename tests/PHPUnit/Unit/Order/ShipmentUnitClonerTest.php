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

namespace Tests\Sylius\MolliePlugin\PHPUnit\Unit\Order;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Shipping\Model\ShipmentUnitInterface;
use Sylius\MolliePlugin\Order\ShipmentUnitCloner;
use Sylius\MolliePlugin\Order\ShipmentUnitClonerInterface;

final class ShipmentUnitClonerTest extends TestCase
{
    private FactoryInterface $unitFactoryMock;

    private ShipmentUnitCloner $shipmentUnitCloner;

    protected function setUp(): void
    {
        $this->unitFactoryMock = $this->createMock(FactoryInterface::class);
        $this->shipmentUnitCloner = new ShipmentUnitCloner($this->unitFactoryMock);
    }

    public function testImplementShipmentUnitClonerInterface(): void
    {
        $this->assertInstanceOf(ShipmentUnitClonerInterface::class, $this->shipmentUnitCloner);
    }

    public function testClonesShipmentUnit(): void
    {
        $unitMock = $this->createMock(ShipmentUnitInterface::class);
        $clonedMock = $this->createMock(ShipmentUnitInterface::class);

        $this->unitFactoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($clonedMock)
        ;

        $clonedMock->expects($this->once())
            ->method('setCreatedAt')
            ->with($this->isInstanceOf(\DateTime::class))
        ;

        $result = $this->shipmentUnitCloner->clone($unitMock);
        $this->assertSame($clonedMock, $result);
    }
}
