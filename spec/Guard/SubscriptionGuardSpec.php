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

namespace spec\SyliusMolliePlugin\Guard;

use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use SyliusMolliePlugin\Guard\SubscriptionGuard;
use SyliusMolliePlugin\Guard\SubscriptionGuardInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

final class SubscriptionGuardSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(SubscriptionGuard::class);
    }

    function it_should_implement_subscription_guard_interface(): void
    {
        $this->shouldImplement(SubscriptionGuardInterface::class);
    }

    function it_is_eligible_for_payments_abort(MollieSubscriptionInterface $subscription): void
    {
        $subscription->getRecentFailedPaymentsCount()->willReturn(3);
        $this->isEligibleForPaymentsAbort($subscription)->shouldReturn(true);

        $subscription->getRecentFailedPaymentsCount()->willReturn(0);
        $this->isEligibleForPaymentsAbort($subscription)->shouldReturn(false);
    }

    function it_is_completable_with_fulfilled_date(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionScheduleInterface $schedule
    ): void {
        $subscription->getSchedules()->willReturn(new ArrayCollection([
           $schedule->getWrappedObject()
        ]));

        $schedule->isFulfilled()->willReturn(true);

        $this->isCompletable($subscription)->shouldReturn(true);
    }

    function it_is_completable_without_fulfilled_date(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionScheduleInterface $schedule
    ): void {
        $subscription->getSchedules()->willReturn(new ArrayCollection([
            $schedule->getWrappedObject()
        ]));

        $schedule->isFulfilled()->willReturn(false);

        $this->isCompletable($subscription)->shouldReturn(false);
    }
}
