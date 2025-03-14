<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Generator;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionScheduleGeneratorInterface
{
    public function generate(MollieSubscriptionInterface $subscription): array;
}
