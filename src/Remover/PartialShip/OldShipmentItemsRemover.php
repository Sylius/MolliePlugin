<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Remover\PartialShip;

use Sylius\MolliePlugin\DTO\PartialShipItems;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\ShipmentInterface;

final class OldShipmentItemsRemover implements OldShipmentItemsRemoverInterface
{
    public function remove(OrderInterface $order, PartialShipItems $shipItems): OrderInterface
    {
        /** @var ShipmentInterface $shipment */
        $shipment = $order->getShipments()->first();

        /** @var OrderItemUnitInterface $unit */
        foreach ($shipment->getUnits() as $unit) {
            $item = $shipItems->findById($unit->getOrderItem()->getId());
            if (null !== $item && 0 < $item->getQuantity()) {
                /** @var ShipmentInterface $oldShipment */
                $oldShipment = $unit->getShipment();

                $oldShipment->removeUnit($unit);
                $item->setQuantity($item->getQuantity() - 1);
            }
        }

        return $order;
    }
}
