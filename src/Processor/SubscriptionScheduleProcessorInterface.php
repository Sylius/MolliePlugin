<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Processor;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionScheduleProcessorInterface
{
    public function process(MollieSubscriptionInterface $subscription): void;

    public function processScheduleGeneration(MollieSubscriptionInterface $subscription): void;
}
