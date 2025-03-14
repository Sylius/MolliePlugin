<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class MollieSubscriptionScheduleFactory implements MollieSubscriptionScheduleFactoryInterface
{
    private FactoryInterface $decoratedFactory;

    public function __construct(FactoryInterface $decoratedFactory)
    {
        $this->decoratedFactory = $decoratedFactory;
    }

    public function createNew(): object
    {
        return $this->decoratedFactory->createNew();
    }

    public function createConfiguredForSubscription(
        MollieSubscriptionInterface $mollieSubscription,
        \DateTime $scheduledDateStart,
        int $index,
        \DateTime $fulfilledDate = null
    ): MollieSubscriptionScheduleInterface {
        /** @var MollieSubscriptionScheduleInterface $schedule */
        $schedule = $this->createNew();
        $schedule->setMollieSubscription($mollieSubscription);
        $schedule->setScheduledDate($scheduledDateStart);
        $schedule->setScheduleIndex($index);
        if (null !== $fulfilledDate) {
            $schedule->setFulfilledDate($fulfilledDate);
        }

        return $schedule;
    }
}
