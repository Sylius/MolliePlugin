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

namespace SyliusMolliePlugin\Factory;

use SyliusMolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use SyliusMolliePlugin\Entity\OrderInterface;

interface PaymentDetailsFactoryInterface
{
    public function createForSubscriptionAndOrder(
        MollieSubscriptionConfigurationInterface $subscriptionConfiguration,
        OrderInterface $order
    ): array;
}
