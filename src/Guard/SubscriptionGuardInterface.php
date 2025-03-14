<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Guard;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionGuardInterface
{
    public function isCompletable(MollieSubscriptionInterface $subscription): bool;
}
