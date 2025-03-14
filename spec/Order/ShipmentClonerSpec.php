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

namespace spec\SyliusMolliePlugin\Order;

use SyliusMolliePlugin\Order\ShipmentCloner;
use SyliusMolliePlugin\Order\ShipmentClonerInterface;
use SyliusMolliePlugin\Order\ShipmentUnitClonerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Shipping\Model\ShippingMethodInterface;

final class ShipmentClonerSpec extends ObjectBehavior
{
    function let(
        FactoryInterface $shipmentFactory,
        ShipmentUnitClonerInterface $shipmentUnitCloner)
    : void {
        $this->beConstructedWith(
            $shipmentFactory,
            $shipmentUnitCloner
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ShipmentCloner::class);
    }

    function it_should_implement_shipment_cloner_interface(): void
    {
        $this->shouldImplement(ShipmentClonerInterface::class);
    }

    function it_clones_shipment(
        ShipmentInterface $shipment,
        ShipmentInterface $clonedShipment,
        FactoryInterface $shipmentFactory,
        ShippingMethodInterface $method
    ): void {
        $shipmentFactory->createNew()->willReturn($clonedShipment);
        $shipment->getMethod()->willReturn($method);

        $dateTime = new \DateTime();
        $dateTime->setDate(2050,12,12);
        $dateTime->setTime(10,48);
        $clonedShipment->getState()->willReturn(ShipmentInterface::STATE_READY);
        $clonedShipment->getTracking()->willReturn(null);
        $clonedShipment->getShippedAt()->willReturn(null);
        $clonedShipment->getMethod()->willReturn($method);
        $clonedShipment->getCreatedAt()->willReturn($dateTime);
        $clonedShipment->getUpdatedAt()->willReturn($dateTime);

        $clonedShipment->setState(ShipmentInterface::STATE_READY)->shouldBeCalled();
        $clonedShipment->setTracking(null)->shouldBeCalled();
        $clonedShipment->setShippedAt(null)->shouldBeCalled();
        $clonedShipment->setMethod($method)->shouldBeCalled();
        $clonedShipment->setCreatedAt(Argument::any())->shouldBeCalled();
        $clonedShipment->setUpdatedAt(Argument::any())->shouldBeCalled();

        $clonedShipment->recalculateAdjustmentsTotal()->shouldBeCalled();

        $this->clone($shipment)->shouldReturn($clonedShipment);
    }
}
