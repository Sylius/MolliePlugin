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

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Factory\OrderItemUnitFactoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class OrderItemCloner implements OrderItemClonerInterface
{
    public function __construct(
        private readonly FactoryInterface $orderItemFactory,
        private readonly OrderItemUnitFactoryInterface $orderItemUnitFactory,
    ) {
    }

    public function clone(
        OrderItemInterface $orderItem,
        OrderInterface $order,
    ): OrderItemInterface {
        /** @var OrderItemInterface $clonedOrderItem */
        $clonedOrderItem = $this->orderItemFactory->createNew();
        $clonedOrderItem->setOrder($order);
        $clonedOrderItem->setProductName($orderItem->getProductName());
        $clonedOrderItem->setUnitPrice($orderItem->getUnitPrice());
        $clonedOrderItem->setVariant($orderItem->getVariant());
        $clonedOrderItem->setVariantName($orderItem->getVariantName());
        $clonedOrderItem->setVersion($orderItem->getVersion());
        $clonedOrderItem->setImmutable(true);

        for ($orderItemQuantity = $orderItem->getQuantity(); 0 < $orderItemQuantity; --$orderItemQuantity) {
            $clonedUnit = $this->orderItemUnitFactory->createForItem($clonedOrderItem);
            $clonedOrderItem->addUnit($clonedUnit);
        }

        $clonedOrderItem->recalculateUnitsTotal();
        $clonedOrderItem->recalculateAdjustmentsTotal();

        return $clonedOrderItem;
    }
}
