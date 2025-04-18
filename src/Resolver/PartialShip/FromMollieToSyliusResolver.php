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

namespace SyliusMolliePlugin\Resolver\PartialShip;

use SyliusMolliePlugin\DTO\PartialShipItem;
use SyliusMolliePlugin\DTO\PartialShipItems;
use SyliusMolliePlugin\Remover\PartialShip\OldShipmentItemsRemoverInterface;
use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class FromMollieToSyliusResolver implements FromMollieToSyliusResolverInterface
{
    /** @var RepositoryInterface */
    private $unitsItemRepository;

    /** @var OldShipmentItemsRemoverInterface */
    private $shipmentItemsRemover;

    public function __construct(
        RepositoryInterface $unitsItemRepository,
        OldShipmentItemsRemoverInterface $shipmentItemsRemover
    ) {
        $this->unitsItemRepository = $unitsItemRepository;
        $this->shipmentItemsRemover = $shipmentItemsRemover;
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
        $itemCollection = $order->getItems()->filter(function (OrderItemInterface $item) use ($itemId): bool {
            return $item->getId() === $itemId;
        });

        $refundedUnits = $this->unitsItemRepository->findBy([
            'orderItem' => $itemCollection->first(),
            'shipment' => null,
        ]);

        return count($refundedUnits);
    }
}
