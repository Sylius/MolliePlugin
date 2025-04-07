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

final class PaymentDetailsFactory implements PaymentDetailsFactoryInterface
{
    public function createForSubscriptionAndOrder(
        MollieSubscriptionConfigurationInterface $subscriptionConfiguration,
        OrderInterface $order,
    ): array {
        $originalDetails = $subscriptionConfiguration->getPaymentDetailsConfiguration();

        return [
            'recurring' => true,
            'cartToken' => null,
            'mandateId' => $subscriptionConfiguration->getMandateId(),
            'metadata' => [
                'gateway' => $originalDetails['metadata']['gateway'],
            ],
        ];
    }
}
