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

namespace Sylius\MolliePlugin\Refund\Checker;

use Sylius\MolliePlugin\Refund\Repository\Query\CreditMemosByOrderNumberDateTimeAndAmountQueryInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Filter\UnitRefundFilterInterface;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;

final class DuplicateRefundTheSameAmountChecker implements DuplicateRefundTheSameAmountCheckerInterface
{
    public function __construct(
        private readonly CreditMemosByOrderNumberDateTimeAndAmountQueryInterface $creditMemoByOrderNumberDateTimeAndAmountQuery,
        private readonly UnitRefundFilterInterface $unitRefundFilter,
    ) {
    }

    public function check(RefundUnits $command): bool
    {
        $now = new \DateTime('now');
        $now->sub(new \DateInterval(self::ONE_HOUR_INTERVAL));

        $creditMemos = $this->creditMemoByOrderNumberDateTimeAndAmountQuery->__invoke(
            $command->orderNumber(),
            $now,
            $this->getTotalAmount($command),
        );

        return 0 !== count($creditMemos);
    }

    private function getTotalAmount(RefundUnits $command): int
    {
        $total = 0;

        foreach ($this->unitRefundFilter->filterUnitRefunds($command->units(), OrderItemUnitRefund::class) as $unit) {
            $total += $unit->total();
        }

        foreach ($this->unitRefundFilter->filterUnitRefunds($command->units(), ShipmentRefund::class) as $shipment) {
            $total += $shipment->total();
        }

        return $total;
    }
}
