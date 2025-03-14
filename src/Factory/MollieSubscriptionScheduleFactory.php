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

namespace Sylius\MolliePlugin\Factory;

use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;

final class MollieSubscriptionScheduleFactory implements MollieSubscriptionScheduleFactoryInterface
{
    public function __construct(private readonly FactoryInterface $decoratedFactory)
    {
    }

    public function createNew(): object
    {
        return $this->decoratedFactory->createNew();
    }

    public function createConfiguredForSubscription(
        MollieSubscriptionInterface $mollieSubscription,
        \DateTime $scheduledDateStart,
        int $index,
        ?\DateTime $fulfilledDate = null,
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
