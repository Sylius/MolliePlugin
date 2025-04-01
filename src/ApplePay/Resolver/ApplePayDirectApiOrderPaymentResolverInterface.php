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

namespace Sylius\MolliePlugin\ApplePay\Resolver;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

interface ApplePayDirectApiOrderPaymentResolverInterface
{
    /** @param array<string, mixed> $details */
    public function resolve(
        OrderInterface $order,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        array $details,
    ): void;
}
