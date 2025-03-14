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

namespace Sylius\MolliePlugin\PaymentFee;

use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Exceptions\UnknownPaymentSurchargeTye;
use Sylius\MolliePlugin\PaymentFee\Types\FixedAmount;
use Sylius\MolliePlugin\PaymentFee\Types\FixedAmountAndPercentage;
use Sylius\MolliePlugin\PaymentFee\Types\Percentage;
use Sylius\Component\Order\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class Calculate
{
    public function __construct(private readonly FixedAmount $fixedAmount, private readonly Percentage $percentage, private readonly FixedAmountAndPercentage $fixedAmountAndPercentage)
    {
    }

    public function calculateFromCart(OrderInterface $order, MollieGatewayConfig $paymentMethod): ?OrderInterface
    {
        if (null === $paymentMethod->getPaymentSurchargeFee() || null === $paymentMethod->getPaymentSurchargeFee()->getType()) {
            return null;
        }

        return $this->calculatePaymentSurcharge($order, $paymentMethod);
    }

    private function calculatePaymentSurcharge(OrderInterface $order, MollieGatewayConfig $paymentMethod): OrderInterface
    {
        Assert::notNull($paymentMethod->getPaymentSurchargeFee());
        if (null === $paymentMethod->getPaymentSurchargeFee()->getType() || ' ' === $paymentMethod->getPaymentSurchargeFee()->getType()) {
            return $order;
        }

        $paymentType = $paymentMethod->getPaymentSurchargeFee()->getType();

        if ($this->fixedAmount->canCalculate($paymentType)) {
            return $this->fixedAmount->calculate($order, $paymentMethod);
        }
        if ($this->percentage->canCalculate($paymentType)) {
            return $this->percentage->calculate($order, $paymentMethod);
        }
        if ($this->fixedAmountAndPercentage->canCalculate($paymentType)) {
            return $this->fixedAmountAndPercentage->calculate($order, $paymentMethod);
        }
        if ('no_fee' === $paymentType) {
            return $order;
        }

        throw new UnknownPaymentSurchargeTye(sprintf('Unknown payment type %s', $paymentType));
    }
}
