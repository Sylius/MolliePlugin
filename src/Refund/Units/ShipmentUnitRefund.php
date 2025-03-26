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
use Sylius\Component\Order\Model\AdjustmentInterface;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;

final class ShipmentUnitRefund implements ShipmentUnitRefundInterface
{
    public function refund(
        OrderInterface $order,
        array $orderItemUnitRefund,
        int $totalToRefund,
    ): array {
        /** @var AdjustmentInterface $refundedShipment */
        $refundedShipment = $order->getAdjustments('shipping')->first();

        $totalRefunded = 0;
        if (0 < count($orderItemUnitRefund)) {
            /** @var OrderItemUnitRefund $item */
            foreach ($orderItemUnitRefund as $item) {
                $totalRefunded += $item->total();
            }

            $totalToRefund -= $totalRefunded;
        }

        if (0 >= $totalToRefund) {
            return [];
        }

        if ($totalToRefund > $refundedShipment->getAmount()) {
            $totalToRefund = $refundedShipment->getAmount();
        }

        return [
            new ShipmentRefund(
                $refundedShipment->getId(),
                $totalToRefund,
            ),
        ];
    }
}
