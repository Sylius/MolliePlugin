<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Generator;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Factory\DatePeriodFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionScheduleFactoryInterface;

final class SubscriptionScheduleGenerator implements SubscriptionScheduleGeneratorInterface
{
    private DatePeriodFactoryInterface $datePeriodFactory;

    private MollieSubscriptionScheduleFactoryInterface $scheduleFactory;

    public function __construct(
        DatePeriodFactoryInterface $datePeriodFactory,
        MollieSubscriptionScheduleFactoryInterface $scheduleFactory
    ) {
        $this->datePeriodFactory = $datePeriodFactory;
        $this->scheduleFactory = $scheduleFactory;
    }

    public function generate(MollieSubscriptionInterface $subscription): array
    {
        $startedAt = new \DateTime();
        $subscription->setStartedAt($startedAt);
        $configuration = $subscription->getSubscriptionConfiguration();

        if (null === $configuration->getInterval()) {
            return [];
        }

        $datePeriods = $this->datePeriodFactory->createForSubscriptionConfiguration(
            $startedAt,
            $configuration->getNumberOfRepetitions(),
            $configuration->getInterval()
        );

        $schedules = [];
        foreach ($datePeriods as $index => $date) {
            $schedules[] = $this->scheduleFactory->createConfiguredForSubscription(
                $subscription,
                $date,
                $index,
                0 === $index ? $startedAt : null
            );
        }

        return $schedules;
    }
}
