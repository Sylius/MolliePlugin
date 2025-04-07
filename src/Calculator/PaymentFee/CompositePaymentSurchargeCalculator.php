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

final class CompositePaymentSurchargeCalculator implements PaymentSurchargeCalculatorInterface
{
    /** @param PaymentSurchargeCalculatorInterface[] $calculators */
    public function __construct(private readonly iterable $calculators)
    {
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
    {
        $paymentType = $paymentMethod->getPaymentSurchargeFee()?->getType() ?? PaymentSurchargeFeeType::NONE;

        foreach ($this->calculators as $calculator) {
            if ($calculator->supports($paymentType)) {
                $calculator->calculate($order, $paymentMethod);

                return;
            }
        }

        throw new UnknownPaymentSurchargeType(sprintf('No calculator supports payment type: %s', $paymentType));
    }

    public function supports(string $type): bool
    {
        return true;
    }
}
