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

namespace SyliusMolliePlugin\Factory;

use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionScheduleInterface;
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
