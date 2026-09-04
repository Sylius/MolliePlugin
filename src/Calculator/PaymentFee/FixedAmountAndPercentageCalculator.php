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

use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Model\AdjustmentInterface;
use Sylius\MolliePlugin\Model\PaymentSurchargeFeeType;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Webmozart\Assert\Assert;

final class FixedAmountAndPercentageCalculator implements PaymentSurchargeCalculatorInterface, PaymentSurchargeAmountCalculatorInterface
{
    public function __construct(
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly PaymentSurchargeAmountCalculatorInterface $percentageCalculator,
        private readonly PaymentSurchargeAmountCalculatorInterface $fixedAmountCalculator,
        private readonly DivisorProviderInterface $divisorProvider,
    ) {
    }

    public function supports(string $type): bool
    {
        return PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE === $type;
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
    {
        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT);
        $adjustment->setAmount($this->calculateAmount($order, $paymentMethod));
        $adjustment->setNeutral(false);
        $order->addAdjustment($adjustment);
    }

    public function calculateAmount(OrderInterface $order, MollieGatewayConfig $paymentMethod): int
    {
        $paymentSurchargeFee = $paymentMethod->getPaymentSurchargeFee();
        Assert::notNull($paymentSurchargeFee);
        Assert::notNull($paymentSurchargeFee->getSurchargeLimit());

        $limit = $paymentSurchargeFee->getSurchargeLimit() * $this->divisorProvider->getDivisor();

        $totalAmount = $this->percentageCalculator->calculateAmount($order, $paymentMethod)
            + $this->fixedAmountCalculator->calculateAmount($order, $paymentMethod);

        if ($totalAmount > $limit) {
            $totalAmount = $limit;
        }

        return (int) ceil($totalAmount);
    }
}
