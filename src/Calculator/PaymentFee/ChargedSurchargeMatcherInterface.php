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
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\UnknownPaymentSurchargeType;

interface ChargedSurchargeMatcherInterface
{
    public function chargedSurcharge(OrderInterface $order): int;

    /** @throws UnknownPaymentSurchargeType */
    public function matches(OrderInterface $order, MollieGatewayConfigInterface $config): bool;

    /**
     * Whether any enabled method of the gateway would keep the order total unchanged.
     *
     * @throws UnknownPaymentSurchargeType
     */
    public function gatewayKeepsTheTotal(OrderInterface $order, GatewayConfigInterface $gateway): bool;
}
