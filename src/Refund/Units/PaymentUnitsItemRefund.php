<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Refund\Units;

use Sylius\MolliePlugin\Calculator\Refund\PaymentRefundCalculatorInterface;
use Sylius\MolliePlugin\DTO\PartialRefundItem;
use Sylius\MolliePlugin\Refund\Generator\PaymentNewUnitRefundGeneratorInterface;
use Sylius\MolliePlugin\Refund\Generator\PaymentRefundedGeneratorInterface;
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
