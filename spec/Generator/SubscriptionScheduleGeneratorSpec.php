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

namespace spec\SyliusMolliePlugin\Generator;

use SyliusMolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use SyliusMolliePlugin\Factory\DatePeriodFactoryInterface;
use SyliusMolliePlugin\Factory\MollieSubscriptionScheduleFactoryInterface;
use SyliusMolliePlugin\Generator\SubscriptionScheduleGenerator;
use SyliusMolliePlugin\Generator\SubscriptionScheduleGeneratorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class SubscriptionScheduleGeneratorSpec extends ObjectBehavior
{
    function let(
        DatePeriodFactoryInterface $datePeriodFactory,
        MollieSubscriptionScheduleFactoryInterface $scheduleFactory
    ): void {
        $this->beConstructedWith(
            $datePeriodFactory,
            $scheduleFactory
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(SubscriptionScheduleGenerator::class);
    }

    function it_should_implement_subscription_shecdule_generator_interface(): void
    {
        $this->shouldImplement(SubscriptionScheduleGeneratorInterface::class);
    }

    function it_generates_subscription_schedule(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        DatePeriodFactoryInterface $datePeriodFactory,
        MollieSubscriptionScheduleFactoryInterface $scheduleFactory,
        MollieSubscriptionScheduleInterface $schedule
    ): void {
        $startedAt = new \DateTime();
        $subscription->getStartedAt()->willReturn($startedAt);
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);

        $configuration->getNumberOfRepetitions()->willReturn(5);
        $configuration->getInterval()->willReturn('month');

        $datePeriodFactory->createForSubscriptionConfiguration(
            Argument::any(),
            5,
            'month'
        )->willReturn([$startedAt]);

        $scheduleFactory->createConfiguredForSubscription(
            $subscription,
            Argument::any(),
           0,
            Argument::any()
        )->willReturn($schedule);

        $subscription->setStartedAt(Argument::any())->shouldBeCalled();
        $schedules = [$schedule->getWrappedObject()];

        $this->generate($subscription)->shouldReturn($schedules);
    }
}
