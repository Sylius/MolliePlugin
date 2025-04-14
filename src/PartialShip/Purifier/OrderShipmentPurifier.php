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

namespace Sylius\MolliePlugin\PartialShip\Purifier;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\MolliePlugin\PartialShip\OrderMolliePartialShipInterface;

final class OrderShipmentPurifier implements OrderShipmentPurifierInterface
{
    public function __construct(private readonly OrderMolliePartialShipInterface $molliePartialShip)
    {
    }

    public function purify(OrderInterface $order): void
    {
        /** @var Collection $shipments */
        $shipments = $order->getShipments();
        $shipmentsToRemove = $shipments->filter(static fn (ShipmentInterface $shipment): bool => ShipmentInterface::STATE_READY === $shipment->getState() && $shipment->getUnits()->isEmpty());

        if (0 === count($shipmentsToRemove)) {
            return;
        }

        foreach ($shipmentsToRemove as $shipmentToRemove) {
            $order->removeShipment($shipmentToRemove);
        }

        $this->molliePartialShip->partialShip($order);
    }
}
