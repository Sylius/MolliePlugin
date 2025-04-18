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

namespace Tests\Sylius\MolliePlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionSchedule;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;

final class MollieSubscriptionScheduleTest extends TestCase
{
    private MollieSubscriptionScheduleInterface $mollieSubscriptionSchedule;

    protected function setUp(): void
    {
        $this->mollieSubscriptionSchedule = new MollieSubscriptionSchedule();
    }

    public function testImplemetsMollieSubscriptionScheduleInterface(): void
    {
        $this->assertInstanceOf(MollieSubscriptionScheduleInterface::class, $this->mollieSubscriptionSchedule);
    }

    public function testGetsMollieSubscription(): void
    {
        $mollieSubscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $this->mollieSubscriptionSchedule->setMollieSubscription($mollieSubscriptionMock);
        $this->assertSame($mollieSubscriptionMock, $this->mollieSubscriptionSchedule->getMollieSubscription());
    }

    public function testGetsScheduledDate(): void
    {
        $timeMock = new \DateTime('2029-12-12');
        $this->mollieSubscriptionSchedule->setScheduledDate($timeMock);
        $this->assertSame($timeMock, $this->mollieSubscriptionSchedule->getScheduledDate());
    }

    public function testGetsFulfilledDate(): void
    {
        $timeMock = new \DateTime('2021-12-12');
        $this->mollieSubscriptionSchedule->setScheduledDate($timeMock);
        $this->assertSame($timeMock, $this->mollieSubscriptionSchedule->getScheduledDate());
    }

    public function testGetsScheduleIndex(): void
    {
        $this->mollieSubscriptionSchedule->setScheduleIndex(15);
        $this->assertSame(15, $this->mollieSubscriptionSchedule->getScheduleIndex());
    }
}
