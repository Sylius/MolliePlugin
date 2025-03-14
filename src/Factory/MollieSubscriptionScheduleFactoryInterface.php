<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface MollieSubscriptionScheduleFactoryInterface extends FactoryInterface
{
    public function createConfiguredForSubscription(
        MollieSubscriptionInterface $mollieSubscription,
        \DateTime $scheduledDateStart,
        int $index,
        \DateTime $fulfilledDate = null
    ): MollieSubscriptionScheduleInterface;
}
