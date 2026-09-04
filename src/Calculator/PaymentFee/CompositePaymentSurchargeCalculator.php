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

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Exceptions\UnknownPaymentSurchargeType;
use Sylius\MolliePlugin\Model\PaymentSurchargeFeeType;

final class CompositePaymentSurchargeCalculator implements PaymentSurchargeCalculatorInterface, PaymentSurchargeAmountCalculatorInterface
{
    /** @param PaymentSurchargeCalculatorInterface[] $calculators */
    public function __construct(private readonly iterable $calculators)
    {
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
    {
        $this->supporting($paymentMethod)->calculate($order, $paymentMethod);
    }

    public function calculateAmount(OrderInterface $order, MollieGatewayConfig $paymentMethod): int
    {
        $calculator = $this->supporting($paymentMethod);

        if (!$calculator instanceof PaymentSurchargeAmountCalculatorInterface) {
            throw new UnknownPaymentSurchargeType(sprintf(
                'Calculator %s cannot report an amount without applying it',
                $calculator::class,
            ));
        }

        return $calculator->calculateAmount($order, $paymentMethod);
    }

    public function supports(string $type): bool
    {
        return true;
    }

    private function supporting(MollieGatewayConfig $paymentMethod): PaymentSurchargeCalculatorInterface
    {
        $paymentType = $paymentMethod->getPaymentSurchargeFee()?->getType() ?? PaymentSurchargeFeeType::NONE;

        foreach ($this->calculators as $calculator) {
            if ($calculator->supports($paymentType)) {
                return $calculator;
            }
        }

        throw new UnknownPaymentSurchargeType(sprintf('No calculator supports payment type: %s', $paymentType));
    }
}
