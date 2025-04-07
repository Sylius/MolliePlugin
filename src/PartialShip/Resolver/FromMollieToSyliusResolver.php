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

namespace Sylius\MolliePlugin\PartialShip\Resolver;

use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Model\DTO\PartialShipItem;
use Sylius\MolliePlugin\Model\DTO\PartialShipItems;
use Sylius\MolliePlugin\PartialShip\Remover\OldShipmentItemsRemoverInterface;

final class FromMollieToSyliusResolver implements FromMollieToSyliusResolverInterface
{
    public function __construct(
        private readonly RepositoryInterface $unitsItemRepository,
        private readonly OldShipmentItemsRemoverInterface $shipmentItemsRemover,
    ) {
    }

    public function resolve(OrderInterface $order, Order $mollieOrder): OrderInterface
    {
        $shipItems = new PartialShipItems();

        foreach ($mollieOrder->lines as $line) {
            if (!property_exists($line, 'status')) {
                throw new \InvalidArgumentException();
            }
            if (self::SHIPPING_STATUS === $line->status) {
                if (!property_exists($line, 'metadata')) {
                    throw new \InvalidArgumentException();
                }
                $itemShippedQuantity = $this->getShippedItemQuantity($order, $line->metadata->item_id);
                $shipItem = new PartialShipItem();

                $shipItem->setId($line->metadata->item_id);
                if (!property_exists($line, 'quantityShipped')) {
                    throw new \InvalidArgumentException();
                }
                $shipItem->setQuantity($line->quantityShipped - $itemShippedQuantity);

                $shipItems->setPartialShipItem($shipItem);
            }
        }

        return $this->shipmentItemsRemover->remove($order, $shipItems);
    }

    private function getShippedItemQuantity(OrderInterface $order, int $itemId): int
    {
        $itemCollection = $order->getItems()->filter(fn (OrderItemInterface $item): bool => $item->getId() === $itemId);

        $refundedUnits = $this->unitsItemRepository->findBy([
            'orderItem' => $itemCollection->first(),
            'shipment' => null,
        ]);

        return count($refundedUnits);
    }
}
