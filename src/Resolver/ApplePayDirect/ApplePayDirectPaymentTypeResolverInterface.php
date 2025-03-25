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

namespace Sylius\MolliePlugin\Resolver\ApplePayDirect;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;

interface ApplePayDirectPaymentTypeResolverInterface
{
    /** @param array<string, mixed> $applePayDirectToken */
    public function resolve(
        MollieGatewayConfigInterface $mollieGatewayConfig,
        PaymentInterface $payment,
        array $applePayDirectToken,
    ): void;
}
