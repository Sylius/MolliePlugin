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

namespace Sylius\MolliePlugin\Cloner;

use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ShipmentCloner implements ShipmentClonerInterface
{
    public function __construct(private readonly FactoryInterface $shipmentFactory)
    {
    }

    public function clone(ShipmentInterface $shipment): ShipmentInterface
    {
        /** @var ShipmentInterface $clonedShipment */
        $clonedShipment = $this->shipmentFactory->createNew();

        $clonedShipment->setState(ShipmentInterface::STATE_READY);
        $clonedShipment->setTracking(null);
        $clonedShipment->setShippedAt(null);
        $clonedShipment->setMethod($shipment->getMethod());
        $clonedShipment->setCreatedAt(new \DateTime());
        $clonedShipment->setUpdatedAt(new \DateTime());

        $clonedShipment->recalculateAdjustmentsTotal();

        return $clonedShipment;
    }
}
