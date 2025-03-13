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

namespace SyliusMolliePlugin\Refund\Units;

use SyliusMolliePlugin\Calculator\Refund\PaymentRefundCalculatorInterface;
use SyliusMolliePlugin\DTO\PartialRefundItem;
use SyliusMolliePlugin\Refund\Generator\PaymentNewUnitRefundGeneratorInterface;
use SyliusMolliePlugin\Refund\Generator\PaymentRefundedGeneratorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;

final class PaymentUnitsItemRefund implements PaymentUnitsItemRefundInterface
{
    /** @var PaymentRefundedGeneratorInterface */
    private $paymentRefundedGenerator;

    /** @var PaymentNewUnitRefundGeneratorInterface */
    private $paymentNewUnitRefundGenerator;

    /** @var PaymentRefundCalculatorInterface */
    private $paymentRefundCalculator;

    public function __construct(
        PaymentRefundedGeneratorInterface $paymentRefundedGenerator,
        PaymentNewUnitRefundGeneratorInterface $paymentNewUnitRefundGenerator,
        PaymentRefundCalculatorInterface $paymentRefundCalculator
    ) {
        $this->paymentRefundedGenerator = $paymentRefundedGenerator;
        $this->paymentNewUnitRefundGenerator = $paymentNewUnitRefundGenerator;
        $this->paymentRefundCalculator = $paymentRefundCalculator;
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
                    $partialRefundItem->getAmountToRefund()
                );
            }
        }

        return $unitsToRefund ?? [];
    }
}
