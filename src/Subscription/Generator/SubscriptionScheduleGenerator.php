<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\MolliePlugin\Subscription\Generator;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Factory\DatePeriodFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionScheduleFactoryInterface;

final class SubscriptionScheduleGenerator implements SubscriptionScheduleGeneratorInterface
{
    public function __construct(
        private readonly DatePeriodFactoryInterface $datePeriodFactory,
        private readonly MollieSubscriptionScheduleFactoryInterface $scheduleFactory,
    ) {
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
            $configuration->getInterval(),
        );

        $schedules = [];
        foreach ($datePeriods as $index => $date) {
            $schedules[] = $this->scheduleFactory->createConfiguredForSubscription(
                $subscription,
                $date,
                $index,
                0 === $index ? $startedAt : null,
            );
        }

        return $schedules;
    }
}
