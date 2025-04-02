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

namespace Sylius\MolliePlugin\Calculator\PaymentFee;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Model\AdjustmentInterface;
use Sylius\MolliePlugin\Model\PaymentSurchargeFeeType;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Webmozart\Assert\Assert;

final class FixedAmountAndPercentageCalculator implements PaymentSurchargeCalculatorInterface
{
    public function __construct(
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly PaymentSurchargeCalculatorInterface $percentageCalculator,
        private readonly PaymentSurchargeCalculatorInterface $fixedAmountCalculator,
        private readonly DivisorProviderInterface $divisorProvider,
    ) {
    }

    public function supports(string $type): bool
    {
        return PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE === $type;
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
    {
        $paymentSurchargeFee = $paymentMethod->getPaymentSurchargeFee();
        Assert::notNull($paymentSurchargeFee);
        Assert::notNull($paymentSurchargeFee->getSurchargeLimit());

        $limit = $paymentSurchargeFee->getSurchargeLimit() * $this->divisorProvider->getDivisor();

        $this->percentageCalculator->calculate($order, $paymentMethod);
        $this->fixedAmountCalculator->calculate($order, $paymentMethod);

        $percentageAmount = $this->getSumOfCalculatedValue($order->getAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT));
        $fixedAmount = $this->getSumOfCalculatedValue($order->getAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT));

        $totalAmount = $percentageAmount + $fixedAmount;

        if ($totalAmount > $limit) {
            $totalAmount = $limit;
        }

        $order->removeAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        $order->removeAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT);

        if (!$order->getAdjustments(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT)->isEmpty()) {
            $order->removeAdjustments(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT);
        }

        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT);
        $adjustment->setAmount((int) ceil($totalAmount));
        $adjustment->setNeutral(false);
        $order->addAdjustment($adjustment);
    }

    private function getSumOfCalculatedValue(Collection $adjustments): float
    {
        $total = 0;
        /** @var AdjustmentInterface $adjustment */
        foreach ($adjustments as $adjustment) {
            $total += $adjustment->getAmount();
        }

        return $total;
    }
}
