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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Order;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Shipping\Model\ShippingMethodInterface;
use SyliusMolliePlugin\Order\ShipmentCloner;
use SyliusMolliePlugin\Order\ShipmentClonerInterface;
use SyliusMolliePlugin\Order\ShipmentUnitClonerInterface;

final class ShipmentClonerTest extends TestCase
{
    private FactoryInterface $shipmentFactoryMock;

    private ShipmentUnitClonerInterface $shipmentUnitClonerMock;

    private ShipmentCloner $shipmentCloner;

    protected function setUp(): void
    {
        $this->shipmentFactoryMock = $this->createMock(FactoryInterface::class);
        $this->shipmentUnitClonerMock = $this->createMock(ShipmentUnitClonerInterface::class);
        $this->shipmentCloner = new ShipmentCloner($this->shipmentFactoryMock, $this->shipmentUnitClonerMock);
    }

    public function testImplementShipmentClonerInterface(): void
    {
        $this->assertInstanceOf(ShipmentClonerInterface::class, $this->shipmentCloner);
    }

    public function testClonesShipment(): void
    {
        $shipmentMock = $this->createMock(ShipmentInterface::class);
        $clonedShipmentMock = $this->createMock(ShipmentInterface::class);
        $methodMock = $this->createMock(ShippingMethodInterface::class);

        $this->shipmentFactoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($clonedShipmentMock)
        ;

        $shipmentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($methodMock)
        ;

        $clonedShipmentMock->expects($this->once())
            ->method('setState')
            ->with(ShipmentInterface::STATE_READY)
        ;
        $clonedShipmentMock->expects($this->once())
            ->method('setTracking')
            ->with(null)
        ;
        $clonedShipmentMock->expects($this->once())
            ->method('setShippedAt')
            ->with(null)
        ;
        $clonedShipmentMock->expects($this->once())
            ->method('setMethod')
            ->with($methodMock)
        ;
        $clonedShipmentMock->expects($this->once())
            ->method('setCreatedAt')
            ->with($this->isInstanceOf(\DateTime::class))
        ;
        $clonedShipmentMock->expects($this->once())
            ->method('setUpdatedAt')
            ->with($this->isInstanceOf(\DateTime::class))
        ;
        $clonedShipmentMock->expects($this->once())
            ->method('recalculateAdjustmentsTotal')
        ;

        $result = $this->shipmentCloner->clone($shipmentMock);
        $this->assertSame($clonedShipmentMock, $result);
    }
}