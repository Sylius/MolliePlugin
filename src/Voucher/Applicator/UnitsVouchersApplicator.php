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

namespace Sylius\MolliePlugin\Voucher\Applicator;

use Sylius\Component\Core\Distributor\IntegerDistributorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderItemUnitInterface;
use Sylius\MolliePlugin\Model\AdjustmentInterface;

final class UnitsVouchersApplicator implements UnitsVouchersApplicatorInterface
{
    public function __construct(
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly IntegerDistributorInterface $distributor,
    ) {
    }

    public function apply(OrderInterface $order, array $promotionAmount): void
    {
        $i = 0;
        foreach ($order->getItems() as $item) {
            $adjustmentAmount = $promotionAmount[$i++];

            if (0 === $adjustmentAmount) {
                continue;
            }

            $this->applyAdjustmentsOnItemUnits($item, $adjustmentAmount);
        }
    }

    private function applyAdjustmentsOnItemUnits(
        OrderItemInterface $item,
        int $itemPromotionAmount,
    ): void {
        $splitPromotionAmount = $this->distributor->distribute($itemPromotionAmount, $item->getQuantity());

        $i = 0;
        foreach ($item->getUnits() as $unit) {
            $promotionAmount = $splitPromotionAmount[$i++];
            if (0 === $promotionAmount) {
                continue;
            }

            $this->addAdjustment($unit, $promotionAmount);
        }
    }

    private function addAdjustment(OrderItemUnitInterface $unit, int $amount): void
    {
        if (!$unit->getAdjustments(AdjustmentInterface::VOUCHER_ADJUSTMENT)->isEmpty()) {
            return;
        }

        /** @var AdjustmentInterface $adjustment */
        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::VOUCHER_ADJUSTMENT);
        $adjustment->setAmount($amount);
        $adjustment->setNeutral(false);

        $unit->addAdjustment($adjustment);
    }
}
