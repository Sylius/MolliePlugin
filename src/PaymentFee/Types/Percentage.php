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

use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Order\AdjustmentInterface;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface;
use Webmozart\Assert\Assert;

final class Percentage implements SurchargeTypeInterface
{
    /** @var AdjustmentFactoryInterface */
    private $adjustmentFactory;

    /** @var DivisorProviderInterface */
    private $divisorProvider;

    public function __construct(
        AdjustmentFactoryInterface $adjustmentFactory,
        DivisorProviderInterface $divisorProvider
    ) {
        $this->adjustmentFactory = $adjustmentFactory;
        $this->divisorProvider = $divisorProvider;
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): OrderInterface
    {
        $paymentSurchargeFee = $paymentMethod->getPaymentSurchargeFee();

        Assert::notNull($paymentSurchargeFee);
        Assert::notNull($paymentSurchargeFee->getSurchargeLimit());
        $limit = $paymentSurchargeFee->getSurchargeLimit() * $this->divisorProvider->getDivisor();
        $percentage = $paymentSurchargeFee->getPercentage();

        Assert::notNull($percentage);
        $amount = ($order->getItemsTotal() / 100) * $percentage;

        if (false === $order->getAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT)->isEmpty()) {
            $order->removeAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT);
        }

        if (0 < $limit && $limit <= $amount) {
            $amount = $limit;
        }

        /** @var AdjustmentInterface $adjustment */
        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::PERCENTAGE_ADJUSTMENT);
        $adjustment->setAmount((int) ceil($amount));
        $adjustment->setNeutral(false);
        $order->addAdjustment($adjustment);

        return $order;
    }

    public function canCalculate(string $type): bool
    {
        return Options::PERCENTAGE === array_search($type, Options::getAvailablePaymentSurchargeFeeType(), true);
    }
}
