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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Factory\DatePeriodFactory;
use Sylius\MolliePlugin\Factory\DatePeriodFactoryInterface;

final class DatePeriodFactoryTest extends TestCase
{
    private DatePeriodFactoryInterface $datePeriodFactory;

    public function setUp(): void
    {
        $this->datePeriodFactory = new DatePeriodFactory();
    }

    public function testImplementsDatePeriodFactoryInterface(): void
    {
        $this->assertInstanceOf(DatePeriodFactoryInterface::class, $this->datePeriodFactory);
    }

    public function testCreatesForSubscriptionConfiguration(): void
    {
        $interval = '1 months';
        $times = 3;
        $startedAt = new \DateTime();

        $dates = [
            $startedAt,
        ];

        for ($i = 1; $i < $times; ++$i) {
            $dates[] = (clone $dates[$i - 1])->modify('1 months');
        }

        $expectedDates = array_map(fn (\DateTime $date) => $date->format('Y-m-d H:i:s'), $dates);
        $actualDates = array_map(fn (\DateTime $date) => $date->format('Y-m-d H:i:s'), $this->datePeriodFactory->createForSubscriptionConfiguration($startedAt, $times, $interval));

        Assert::assertSame($expectedDates, $actualDates);
    }
}
