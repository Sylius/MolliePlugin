<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

interface DatePeriodFactoryInterface
{
    public function createForSubscriptionConfiguration(
        \DateTime $start,
        int $times,
        string $interval
    ): array;
}
