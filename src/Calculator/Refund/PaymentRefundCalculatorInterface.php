<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Calculator\Refund;

use Sylius\MolliePlugin\DTO\PartialRefundItems;

interface PaymentRefundCalculatorInterface
{
    public function calculate(PartialRefundItems $partialRefundItems, int $totalToRefund): PartialRefundItems;
}
