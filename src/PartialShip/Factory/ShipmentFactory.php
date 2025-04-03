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

namespace Sylius\MolliePlugin\PartialShip\Factory;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ShipmentFactory implements ShipmentFactoryInterface
{
    public function __construct(private readonly FactoryInterface $baseFactory)
    {
    }

    public function createWithOrderInventorySourceAndMethodFromShipment(ShipmentInterface $shipment): ShipmentInterface
    {
        /** @var ShipmentInterface $newShipment */
        $newShipment = $this->baseFactory->createNew();

        $newShipment->setMethod($shipment->getMethod());

        /** @var OrderInterface $order */
        $order = $shipment->getOrder();
        $order->addShipment($newShipment);

        return $newShipment;
    }
}
