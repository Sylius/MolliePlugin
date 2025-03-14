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

namespace SyliusMolliePlugin\Calculator\Refund;

use SyliusMolliePlugin\DTO\PartialRefundItem;
use SyliusMolliePlugin\DTO\PartialRefundItems;

final class PaymentRefundCalculator implements PaymentRefundCalculatorInterface
{
    public function calculate(PartialRefundItems $partialRefundItems, int $totalToRefund): PartialRefundItems
    {
        /** @var PartialRefundItem $partialRefundItem */
        foreach ($partialRefundItems->getPartialRefundItems() as $partialRefundItem) {
            if (0 < $partialRefundItem->getAvailableAmountToRefund()) {
                $totalToRefund = $partialRefundItem->setAmountToRefund($totalToRefund);
                if (0 === $totalToRefund) {
                    break;
                }
            }
        }

        return $partialRefundItems;
    }
}
