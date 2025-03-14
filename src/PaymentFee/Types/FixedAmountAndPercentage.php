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

namespace Sylius\MolliePlugin\PaymentFee\Types;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Order\AdjustmentInterface;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface;
use Webmozart\Assert\Assert;

final class FixedAmountAndPercentage implements SurchargeTypeInterface
{
    public function __construct(private readonly AdjustmentFactoryInterface $adjustmentFactory, private readonly Percentage $percentage, private readonly FixedAmount $fixedAmount, private readonly DivisorProviderInterface $divisorProvider)
    {
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): OrderInterface
    {
        $paymentSurchargeFee = $paymentMethod->getPaymentSurchargeFee();
        Assert::notNull($paymentSurchargeFee);
        Assert::notNull($paymentSurchargeFee->getSurchargeLimit());
        $limit = $paymentSurchargeFee->getSurchargeLimit() * $this->divisorProvider->getDivisor();

        $percentage = $this->percentage->calculate($order, $paymentMethod);
        $fixed = $this->fixedAmount->calculate($order, $paymentMethod);

        $percentageAmount = $this->getSumOfCalculatedValue($percentage->getAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT));
        $fixAmount = $this->getSumOfCalculatedValue($fixed->getAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT));

        $amount = $percentageAmount + $fixAmount;

        if ($amount > $limit) {
            $amount = $limit;
        }

        $order->removeAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        $order->removeAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT);

        if (false === $order->getAdjustments(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT)->isEmpty()) {
            $order->removeAdjustments(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT);
        }

        /** @var AdjustmentInterface $adjustment */
        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT);
        $adjustment->setAmount((int) ceil($amount));
        $adjustment->setNeutral(false);
        $order->addAdjustment($adjustment);

        return $order;
    }

    public function canCalculate(string $type): bool
    {
        return Options::FIXED_FEE_AND_PERCENTAGE === array_search($type, Options::getAvailablePaymentSurchargeFeeType(), true);
    }

    private function getSumOfCalculatedValue(Collection $adjustments): float
    {
        $value = 0;

        /** @var AdjustmentInterface $adjustment */
        foreach ($adjustments as $adjustment) {
            $value += $adjustment->getAmount();
        }

        return $value;
    }
}
