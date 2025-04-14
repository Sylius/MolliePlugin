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

namespace Sylius\MolliePlugin\Refund\Units;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Model\DTO\PartialRefundItems;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\RefundType;
use Sylius\RefundPlugin\Model\UnitRefundInterface;

final class UnitsItemOrderRefund implements UnitsItemOrderRefundInterface
{
    public function __construct(private readonly RepositoryInterface $refundUnitsRepository)
    {
    }

    public function refund(OrderInterface $order, PartialRefundItems $partialRefundItems): array
    {
        $units = $order->getItemUnits();

        $unitsToRefund = [];

        /** @var OrderItemUnitInterface $unit */
        foreach ($units as $unit) {
            if (true === $this->hasUnitRefunded($order, $unit->getId())) {
                continue;
            }

            $partialItem = $partialRefundItems->findById($unit->getOrderItem()->getId());
            if (null !== $partialItem) {
                $unitsToRefund[] = new OrderItemUnitRefund(
                    $unit->getId(),
                    $unit->getTotal(),
                );

                $partialRefundItems->removeItem($partialItem);
            }
        }

        return $unitsToRefund;
    }

    public function getActualRefundedQuantity(OrderInterface $order, int $itemId): int
    {
        return count(array_filter($this->getActualRefunded($order, $itemId)));
    }

    /** @return array<array-key, UnitRefundInterface|null> */
    private function getActualRefunded(OrderInterface $order, int $itemId): array
    {
        $units = $order->getItemUnits();

        $refundedUnits = [];
        foreach ($units as $unit) {
            if ($itemId === $unit->getOrderItem()->getId()) {
                /** @var UnitRefundInterface|null $refundUnit */
                $refundUnit = $this->refundUnitsRepository->findOneBy([
                    'order' => $order->getId(),
                    'refundedUnitId' => $unit->getId(),
                    'type' => RefundType::orderItemUnit(),
                ]);

                $refundedUnits[] = $refundUnit;
            }
        }

        return $refundedUnits;
    }

    private function hasUnitRefunded(OrderInterface $order, int $unitId): bool
    {
        $unitRefunded = $this->refundUnitsRepository->findOneBy([
            'order' => $order->getId(),
            'refundedUnitId' => $unitId,
            'type' => RefundType::orderItemUnit(),
        ]);

        return null !== $unitRefunded;
    }
}
