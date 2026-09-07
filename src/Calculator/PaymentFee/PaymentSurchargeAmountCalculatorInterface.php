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

interface PaymentSurchargeAmountCalculatorInterface
{
    /** The surcharge this method would add to the order, in the smallest currency unit. */
    public function calculateAmount(OrderInterface $order, MollieGatewayConfig $paymentMethod): int;
}
