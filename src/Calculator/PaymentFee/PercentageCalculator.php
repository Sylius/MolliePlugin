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

final class PercentageCalculator implements PaymentSurchargeCalculatorInterface
{
    public function __construct(
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly DivisorProviderInterface $divisorProvider,
    ) {
    }

    public function supports(string $type): bool
    {
        return PaymentSurchargeFeeType::PERCENTAGE === $type;
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
    {
        $paymentSurchargeFee = $paymentMethod->getPaymentSurchargeFee();
        Assert::notNull($paymentSurchargeFee);
        Assert::notNull($paymentSurchargeFee->getSurchargeLimit());

        $limit = $paymentSurchargeFee->getSurchargeLimit() * $this->divisorProvider->getDivisor();
        $percentage = $paymentSurchargeFee->getPercentage();
        Assert::notNull($percentage);

        $amount = ($order->getItemsTotal() / 100) * $percentage;

        if (!$order->getAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT)->isEmpty()) {
            $order->removeAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT);
        }

        if ($limit > 0 && $amount > $limit) {
            $amount = $limit;
        }

        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::PERCENTAGE_ADJUSTMENT);
        $adjustment->setAmount((int) ceil($amount));
        $adjustment->setNeutral(false);
        $order->addAdjustment($adjustment);
    }
}
