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
use Sylius\MolliePlugin\Model\DTO\PartialRefundItem;
use Sylius\MolliePlugin\Refund\Calculator\PaymentRefundCalculatorInterface;
use Sylius\MolliePlugin\Refund\Generator\PaymentNewUnitRefundGeneratorInterface;
use Sylius\MolliePlugin\Refund\Generator\PaymentRefundedGeneratorInterface;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;

final class PaymentUnitsItemRefund implements PaymentUnitsItemRefundInterface
{
    public function __construct(
        private readonly PaymentRefundedGeneratorInterface $paymentRefundedGenerator,
        private readonly PaymentNewUnitRefundGeneratorInterface $paymentNewUnitRefundGenerator,
        private readonly PaymentRefundCalculatorInterface $paymentRefundCalculator,
    ) {
    }

    public function refund(OrderInterface $order, int $totalToRefund): array
    {
        $partialRefundItems = $this->paymentRefundedGenerator->generate($order);
        $partialRefundItems = $this->paymentNewUnitRefundGenerator->generate($order, $partialRefundItems);
        $partialRefundItems = $this->paymentRefundCalculator->calculate($partialRefundItems, $totalToRefund);

        $unitsToRefund = [];
        /** @var PartialRefundItem $partialRefundItem */
        foreach ($partialRefundItems->getPartialRefundItems() as $partialRefundItem) {
            if (0 < $partialRefundItem->getAmountToRefund()) {
                $unitsToRefund[] = new OrderItemUnitRefund(
                    $partialRefundItem->getId(),
                    $partialRefundItem->getAmountToRefund(),
                );
            }
        }

        return $unitsToRefund;
    }
}
