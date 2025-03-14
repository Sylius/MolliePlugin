<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\PartialShip;

use Sylius\MolliePlugin\DTO\PartialShipItem;
use Sylius\MolliePlugin\DTO\PartialShipItems;
use Sylius\MolliePlugin\Remover\PartialShip\OldShipmentItemsRemoverInterface;
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
