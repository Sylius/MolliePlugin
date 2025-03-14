<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

final class PaymentDetailsFactory implements PaymentDetailsFactoryInterface
{
    public function createForSubscriptionAndOrder(
        MollieSubscriptionConfigurationInterface $subscriptionConfiguration,
        OrderInterface $order
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
