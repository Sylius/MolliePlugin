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

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

interface PaymentDetailsFactoryInterface
{
    /**
     * @return array{
     *     recurring: bool,
     *     cartToken: null,
     *     mandateId: string|null,
     *     metadata: array{gateway: string|mixed},
     * }
     */
    public function createForSubscriptionAndOrder(
        MollieSubscriptionConfigurationInterface $subscriptionConfiguration,
        OrderInterface $order,
    ): array;
}
