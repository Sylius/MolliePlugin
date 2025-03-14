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

namespace spec\Sylius\MolliePlugin\Entity;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionSchedule;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use PhpSpec\ObjectBehavior;

final class MollieSubscriptionScheduleSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieSubscriptionSchedule::class);
    }

    function it_should_implemets_mollie_subscription_schedule_interface(): void
    {
        $this->shouldImplement(MollieSubscriptionScheduleInterface::class);
    }

    function it_gets_mollie_subscription(
        MollieSubscriptionInterface $mollieSubscription
    ): void {
        $this->setMollieSubscription($mollieSubscription);
        $this->getMollieSubscription()->shouldReturn($mollieSubscription);
    }

    function it_gets_scheduled_date(\DateTime $time): void
    {
        $time->setDate(2029,12,12);
        $this->setScheduledDate($time);
        $this->getScheduledDate()->shouldReturn($time);
    }

    function it_gets_fulfilled_date(\DateTime $time): void
    {
        $time->setDate(2021,12,12);
        $this->setScheduledDate($time);
        $this->getScheduledDate()->shouldReturn($time);
    }

    function it_gets_schedule_index(): void
    {
        $this->setScheduleIndex(15);
        $this->getScheduleIndex()->shouldReturn(15);
    }
}
