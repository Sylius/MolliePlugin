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

namespace Tests\Sylius\MolliePlugin\Unit\Generator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use Sylius\MolliePlugin\Factory\DatePeriodFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionScheduleFactoryInterface;
use Sylius\MolliePlugin\Subscription\Generator\SubscriptionScheduleGenerator;
use Sylius\MolliePlugin\Subscription\Generator\SubscriptionScheduleGeneratorInterface;

final class SubscriptionScheduleGeneratorTest extends TestCase
{
    private MockObject $datePeriodFactoryMock;

    private MockObject $scheduleFactoryMock;

    private SubscriptionScheduleGenerator $subscriptionScheduleGenerator;

    protected function setUp(): void
    {
        $this->datePeriodFactoryMock = $this->createMock(DatePeriodFactoryInterface::class);
        $this->scheduleFactoryMock = $this->createMock(MollieSubscriptionScheduleFactoryInterface::class);
        $this->subscriptionScheduleGenerator = new SubscriptionScheduleGenerator(
            $this->datePeriodFactoryMock,
            $this->scheduleFactoryMock,
        );
    }

    public function testImplementSubscriptionScheduleGeneratorInterface(): void
    {
        $this->assertInstanceOf(
            SubscriptionScheduleGeneratorInterface::class,
            $this->subscriptionScheduleGenerator,
        );
    }

    public function testGeneratesSubscriptionSchedule(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $configurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $scheduleMock = $this->createMock(MollieSubscriptionScheduleInterface::class);

        $startedAt = new \DateTime();

        $subscriptionMock->expects($this->once())
            ->method('getSubscriptionConfiguration')
            ->willReturn($configurationMock);

        $configurationMock->expects($this->once())
            ->method('getNumberOfRepetitions')
            ->willReturn(5);

        $configurationMock->expects($this->exactly(2))
            ->method('getInterval')
            ->willReturn('month');

        $datePeriods = [
            (clone $startedAt)->setTime((int) $startedAt->format('H'), (int) $startedAt->format('i'), (int) $startedAt->format('s')),
            (clone $startedAt)->modify('+1 month')->setTime(0, 0, 0),
            (clone $startedAt)->modify('+2 months')->setTime(0, 0, 0),
            (clone $startedAt)->modify('+3 months')->setTime(0, 0, 0),
            (clone $startedAt)->modify('+4 months')->setTime(0, 0, 0),
        ];

        $this->datePeriodFactoryMock->expects($this->once())
            ->method('createForSubscriptionConfiguration')
            ->with(
                $this->callback(fn ($actualStartedAt) => $actualStartedAt instanceof \DateTime),
                5,
                'month',
            )
            ->willReturn($datePeriods);

        $this->scheduleFactoryMock->expects($this->exactly(5))
            ->method('createConfiguredForSubscription')
            ->withConsecutive(
                [$subscriptionMock, $this->callback(fn ($d) => $d->format('Y-m-d H:i:s') === $datePeriods[0]->format('Y-m-d H:i:s')), 0, $this->callback(fn ($d) => $d?->format('Y-m-d H:i:s') === $startedAt->format('Y-m-d H:i:s'))],
                [$subscriptionMock, $this->callback(fn ($d) => $d->format('Y-m-d H:i:s') === $datePeriods[1]->format('Y-m-d H:i:s')), 1, null],
                [$subscriptionMock, $this->callback(fn ($d) => $d->format('Y-m-d H:i:s') === $datePeriods[2]->format('Y-m-d H:i:s')), 2, null],
                [$subscriptionMock, $this->callback(fn ($d) => $d->format('Y-m-d H:i:s') === $datePeriods[3]->format('Y-m-d H:i:s')), 3, null],
                [$subscriptionMock, $this->callback(fn ($d) => $d->format('Y-m-d H:i:s') === $datePeriods[4]->format('Y-m-d H:i:s')), 4, null],
            )
            ->willReturnOnConsecutiveCalls(
                $scheduleMock,
                $scheduleMock,
                $scheduleMock,
                $scheduleMock,
                $scheduleMock,
            );

        $subscriptionMock->expects($this->once())
            ->method('setStartedAt')
            ->with($this->callback(fn ($actualStartedAt) => $actualStartedAt->format('Y-m-d H:i:s') === $startedAt->format('Y-m-d H:i:s')));

        $schedules = $this->subscriptionScheduleGenerator->generate($subscriptionMock);

        $this->assertCount(5, $schedules);
        $this->assertContainsOnlyInstancesOf(MollieSubscriptionScheduleInterface::class, $schedules);
    }
}
