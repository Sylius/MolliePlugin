<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Processor;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionProcessorInterface
{
    public function processNextPayment(MollieSubscriptionInterface $subscription): void;

    public function processNextSubscriptionPayment(MollieSubscriptionInterface $subscription): void;
}
