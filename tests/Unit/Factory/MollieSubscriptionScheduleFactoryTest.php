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

namespace Tests\Sylius\MolliePlugin\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionScheduleFactory;
use Sylius\MolliePlugin\Factory\MollieSubscriptionScheduleFactoryInterface;

final class MollieSubscriptionScheduleFactoryTest extends TestCase
{
    private FactoryInterface $decoratedFactoryMock;

    private MollieSubscriptionScheduleFactory $mollieSubscriptionScheduleFactory;

    protected function setUp(): void
    {
        $this->decoratedFactoryMock = $this->createMock(FactoryInterface::class);
        $this->mollieSubscriptionScheduleFactory = new MollieSubscriptionScheduleFactory($this->decoratedFactoryMock);
    }

    public function testImplementsMollieSubscriptionScheduleFactoryInterface(): void
    {
        $this->assertInstanceOf(MollieSubscriptionScheduleFactoryInterface::class, $this->mollieSubscriptionScheduleFactory);
    }

    public function testCreatesConfiguredForSubscriptionWhenFulfilledDateIsNull(): void
    {
        $scheduleMock = $this->createMock(MollieSubscriptionScheduleInterface::class);
        $mollieSubscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $scheduledDateStart = new \DateTime();

        $this->decoratedFactoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($scheduleMock);

        $scheduleMock->expects($this->once())
            ->method('setMollieSubscription')
            ->with($mollieSubscriptionMock);

        $scheduleMock->expects($this->once())
            ->method('setScheduledDate')
            ->with($scheduledDateStart);

        $scheduleMock->expects($this->once())
            ->method('setScheduleIndex')
            ->with(9);

        $result = $this->mollieSubscriptionScheduleFactory->createConfiguredForSubscription(
            $mollieSubscriptionMock,
            $scheduledDateStart,
            9,
        );

        $this->assertSame($scheduleMock, $result);
    }

    public function testCreatesConfiguredForSubscriptionWhenFulfilledDateIsNotNull(): void
    {
        $scheduleMock = $this->createMock(MollieSubscriptionScheduleInterface::class);
        $mollieSubscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $scheduledDateStart = new \DateTime();
        $fulfilledDate = new \DateTime('2012-12-12');

        $this->decoratedFactoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($scheduleMock)
        ;

        $scheduleMock->expects($this->once())
            ->method('setMollieSubscription')
            ->with($mollieSubscriptionMock)
        ;

        $scheduleMock->expects($this->once())
            ->method('setScheduledDate')
            ->with($scheduledDateStart)
        ;

        $scheduleMock->expects($this->once())
            ->method('setScheduleIndex')
            ->with(9)
        ;

        $scheduleMock->expects($this->once())
            ->method('setFulfilledDate')
            ->with($fulfilledDate)
        ;

        $result = $this->mollieSubscriptionScheduleFactory->createConfiguredForSubscription(
            $mollieSubscriptionMock,
            $scheduledDateStart,
            9,
            $fulfilledDate,
        );

        $this->assertSame($scheduleMock, $result);
    }
}
