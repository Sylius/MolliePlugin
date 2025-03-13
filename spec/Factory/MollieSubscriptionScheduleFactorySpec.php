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

namespace spec\SyliusMolliePlugin\Factory;

use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use SyliusMolliePlugin\Factory\MollieSubscriptionScheduleFactory;
use SyliusMolliePlugin\Factory\MollieSubscriptionScheduleFactoryInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class MollieSubscriptionScheduleFactorySpec extends ObjectBehavior
{
    function let(FactoryInterface $decoratedFactory): void
    {
        $this->beConstructedWith($decoratedFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MollieSubscriptionScheduleFactory::class);
    }

    function it_should_implements_mollie_subscription_schedule_factory_interface(): void
    {
        $this->shouldImplement(MollieSubscriptionScheduleFactoryInterface::class);
    }

    function it_creates_configured_for_subscription_when_fulfilled_date_is_null(
        FactoryInterface $decoratedFactory,
        MollieSubscriptionScheduleInterface $schedule,
        MollieSubscriptionInterface $mollieSubscription
    ): void {
        $scheduledDateStart = new \DateTime();
        $decoratedFactory->createNew()->willReturn($schedule);
        $schedule->setMollieSubscription($mollieSubscription);
        $schedule->setScheduledDate($scheduledDateStart);
        $schedule->setScheduleIndex(9);

        $this->createConfiguredForSubscription(
            $mollieSubscription,
            $scheduledDateStart,
            9
        )->shouldReturn($schedule);
    }

    function it_creates_configured_for_subscription_when_fulfilled_date_is_not_null(
        FactoryInterface $decoratedFactory,
        MollieSubscriptionScheduleInterface $schedule,
        MollieSubscriptionInterface $mollieSubscription
    ): void {
        $scheduledDateStart = new \DateTime();
        $fulfilledDate = new \DateTime;
        $fulfilledDate->setDate(2012,12,12);
        $decoratedFactory->createNew()->willReturn($schedule);

        $schedule->setMollieSubscription($mollieSubscription);
        $schedule->setScheduledDate($scheduledDateStart);
        $schedule->setScheduleIndex(9);

        $schedule->setFulfilledDate($fulfilledDate);
        Assert::notNull($fulfilledDate);

        $fulfilledDate = null;
        $schedule->setFulfilledDate($fulfilledDate);
        Assert::null($fulfilledDate);

        $this->createConfiguredForSubscription(
            $mollieSubscription,
            $scheduledDateStart,
            9,
            $fulfilledDate
        )->shouldReturn($schedule);
    }
}
