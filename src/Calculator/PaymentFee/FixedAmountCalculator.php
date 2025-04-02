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

final class FixedAmountCalculator implements PaymentSurchargeCalculatorInterface
{
    public function __construct(
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly DivisorProviderInterface $divisorProvider,
    ) {
    }

    public function supports(string $type): bool
    {
        return PaymentSurchargeFeeType::FIXED === $type;
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
    {
        Assert::notNull($paymentMethod->getPaymentSurchargeFee());
        $fixedAmount = $paymentMethod->getPaymentSurchargeFee()->getFixedAmount();
        Assert::notNull($fixedAmount);

        if (false === $order->getAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT)->isEmpty()) {
            $order->removeAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        }

        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        $adjustment->setAmount((int) ($fixedAmount * $this->divisorProvider->getDivisor()));
        $adjustment->setNeutral(false);

        $order->addAdjustment($adjustment);
    }
}
