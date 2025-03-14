<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Calculator\Refund;

use Sylius\MolliePlugin\DTO\PartialRefundItem;
use Sylius\MolliePlugin\DTO\PartialRefundItems;

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
