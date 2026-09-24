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

        $matcher = $this->exactly(5);
        $this->scheduleFactoryMock->expects($matcher)
            ->method('createConfiguredForSubscription')
            ->willReturnCallback(
                function (
                    MollieSubscriptionInterface $subscription,
                    \DateTimeInterface $scheduledAt,
                    int $index,
                    ?\DateTimeInterface $processedAt,
                ) use ($matcher, $subscriptionMock, $datePeriods, $startedAt, $scheduleMock): MollieSubscriptionScheduleInterface {
                    $invocation = $matcher->numberOfInvocations();

                    $this->assertSame($subscriptionMock, $subscription);
                    $this->assertSame($datePeriods[$invocation - 1]->format('Y-m-d H:i:s'), $scheduledAt->format('Y-m-d H:i:s'));
                    $this->assertSame($invocation - 1, $index);

                    if (1 === $invocation) {
                        $this->assertSame($startedAt->format('Y-m-d H:i:s'), $processedAt?->format('Y-m-d H:i:s'));
                    } else {
                        $this->assertNull($processedAt);
                    }

                    return $scheduleMock;
                },
            );

        $subscriptionMock->expects($this->once())
            ->method('setStartedAt')
            ->with($this->callback(fn ($actualStartedAt) => $actualStartedAt->format('Y-m-d H:i:s') === $startedAt->format('Y-m-d H:i:s')));

        $schedules = $this->subscriptionScheduleGenerator->generate($subscriptionMock);

        $this->assertCount(5, $schedules);
        $this->assertContainsOnlyInstancesOf(MollieSubscriptionScheduleInterface::class, $schedules);
    }
}
