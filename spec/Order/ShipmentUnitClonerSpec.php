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

use SyliusMolliePlugin\Order\ShipmentUnitCloner;
use SyliusMolliePlugin\Order\ShipmentUnitClonerInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Shipping\Model\ShipmentUnitInterface;

final class ShipmentUnitClonerSpec extends ObjectBehavior
{
    function let(FactoryInterface $unitFactory): void
    {
        $this->beConstructedWith($unitFactory);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ShipmentUnitCloner::class);
    }

    function it_should_have_implement_shipment_unit_cloner_interface(): void
    {
        $this->shouldImplement(ShipmentUnitClonerInterface::class);
    }

    function it_clones_shipment_unit(
        ShipmentUnitInterface $unit,
        FactoryInterface $unitFactory,
        ShipmentUnitInterface $cloned
    ): void {
        $unitFactory->createNew()->willReturn($cloned);
        $cloned->setCreatedAt(new \DateTime());

        $this->clone($unit)->shouldReturn($cloned);
    }
}
