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

namespace SyliusMolliePlugin\Helper;

use SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentDescriptionInterface
{
    public const PAYPAL_DESCRIPTION = 'Order';

    public function getPaymentDescription(
        PaymentInterface $payment,
        MollieGatewayConfigInterface $methodConfig,
        OrderInterface $order
    ): string;
}
