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

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Exceptions\UnknownPaymentSurchargeTye;
use Sylius\MolliePlugin\PaymentFee\Calculator\PaymentSurchargeCalculatorInterface;

final class CompositePaymentSurchargeCalculator
{
    /**
     * @param PaymentSurchargeCalculatorInterface[] $calculators
     */
    public function __construct(private readonly iterable $calculators)
    {
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): ?OrderInterface
    {
        if (null === $paymentMethod->getPaymentSurchargeFee() || null === $paymentMethod->getPaymentSurchargeFee()->getType()) {
            return null;
        }

        return $this->calculatePaymentSurcharge($order, $paymentMethod);
    }

    private function calculatePaymentSurcharge(OrderInterface $order, MollieGatewayConfig $paymentMethod): OrderInterface
    {
        $paymentType = $paymentMethod->getPaymentSurchargeFee()->getType();

        foreach ($this->calculators as $calculator) {
            if ($calculator->supports($paymentType)) {
                return $calculator->calculate($order, $paymentMethod);
            }
        }

        if ('no_fee' === $paymentType) {
            return $order;
        }

        throw new UnknownPaymentSurchargeTye(sprintf('Unknown payment type %s', $paymentType));
    }
}
