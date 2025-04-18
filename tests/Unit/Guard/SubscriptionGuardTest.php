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

namespace Tests\Sylius\MolliePlugin\Unit\Guard;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;
use Sylius\MolliePlugin\Subscription\Guard\SubscriptionGuard;
use Sylius\MolliePlugin\Subscription\Guard\SubscriptionGuardInterface;

final class SubscriptionGuardTest extends TestCase
{
    private SubscriptionGuardInterface $subscriptionGuard;

    protected function setUp(): void
    {
        $this->subscriptionGuard = new SubscriptionGuard();
    }

    public function testImplementSubscriptionGuardInterface(): void
    {
        $this->assertInstanceOf(SubscriptionGuardInterface::class, $this->subscriptionGuard);
    }

    public function testEligibleForPaymentsAbort(): void
    {
        /** @var MollieSubscriptionInterface|MockObject $subscriptionMock */
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);

        $subscriptionMock->expects($this->once())
            ->method('getRecentFailedPaymentsCount')
            ->willReturn(0)
        ;

        $this->assertFalse($this->subscriptionGuard->isEligibleForPaymentsAbort($subscriptionMock));
    }

    public function testCompletableWithFulfilledDate(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $scheduleMock = $this->createMock(MollieSubscriptionScheduleInterface::class);

        $subscriptionMock->expects($this->once())
            ->method('getSchedules')
            ->willReturn(new ArrayCollection([$scheduleMock]))
        ;

        $scheduleMock->expects($this->once())
            ->method('isFulfilled')
            ->willReturn(true)
        ;

        $this->assertTrue($this->subscriptionGuard->isCompletable($subscriptionMock));
    }

    public function testCompletableWithoutFulfilledDate(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $scheduleMock = $this->createMock(MollieSubscriptionScheduleInterface::class);

        $subscriptionMock->expects($this->once())
            ->method('getSchedules')
            ->willReturn(new ArrayCollection([$scheduleMock]))
        ;

        $scheduleMock->expects($this->once())
            ->method('isFulfilled')
            ->willReturn(false)
        ;

        $this->assertFalse($this->subscriptionGuard->isCompletable($subscriptionMock));
    }
}
