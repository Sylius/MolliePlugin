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

use Doctrine\Common\Collections\Collection;
use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\MolliePlugin\Model\DTO\PartialShipItem;
use Sylius\MolliePlugin\Model\DTO\PartialShipItems;
use Webmozart\Assert\Assert;

final class FromSyliusToMollieLinesResolver implements FromSyliusToMollieLinesResolverInterface
{
    public function resolve(Collection $units, Order $mollieOrder): PartialShipItems
    {
        $shipItems = new PartialShipItems();

        /** @var OrderItemUnitInterface $unit */
        foreach ($units as $unit) {
            $lineId = $this->getLineIdFromMollie($mollieOrder, $unit->getOrderItem()->getId());
            Assert::notNull($lineId);

            $shipItem = new PartialShipItem();
            $shipItem->setId($unit->getOrderItem()->getId());
            $shipItem->setLineId($lineId);
            $shipItem->setQuantity(1);

            $shipItems->setPartialShipItem($shipItem);
        }

        return $shipItems;
    }

    private function getLineIdFromMollie(Order $mollieOrder, int $itemId): ?string
    {
        foreach ($mollieOrder->lines as $line) {
            if (!property_exists($line, 'metadata')) {
                throw new \InvalidArgumentException();
            }
            if ($itemId === $line->metadata->item_id) {
                if (!property_exists($line, 'id')) {
                    throw new \InvalidArgumentException();
                }

                return (string) $line->id;
            }
        }

        return null;
    }
}
