<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\PaymentProcessing;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface CancelRecurringSubscriptionProcessorInterface
{
    public function process(MollieSubscriptionInterface $subscription): void;
}
