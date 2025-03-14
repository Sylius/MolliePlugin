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

namespace SyliusMolliePlugin\Resolver\ApplePayDirect;

use SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface ApplePayDirectPaymentTypeResolverInterface
{
    public function resolve(
        MollieGatewayConfigInterface $mollieGatewayConfig,
        PaymentInterface $payment,
        array $applePayDirectToken
    ): void;
}
