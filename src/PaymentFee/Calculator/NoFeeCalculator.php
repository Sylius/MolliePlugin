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

namespace Sylius\MolliePlugin\PaymentFee\Calculator;

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\PaymentFee\PaymentSurchargeFeeType;

final class NoFeeCalculator implements PaymentSurchargeCalculatorInterface
{
    public function supports(string $type): bool
    {
        return PaymentSurchargeFeeType::NONE === $type;
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
    {
        // noop
    }
}
