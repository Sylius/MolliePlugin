<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Factory\DatePeriodFactory;
use Sylius\MolliePlugin\Factory\DatePeriodFactoryInterface;
use PhpSpec\ObjectBehavior;

final class DatePeriodFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(DatePeriodFactory::class);
    }

    function it_should_implements_date_period_factory_interface(): void
    {
        $this->shouldImplement(DatePeriodFactoryInterface::class);
    }

    function it_creates_for_subscription_configuration(): void
    {
        $interval = '1 months';
        $times = 3;
        $startedAt = new \DateTime();

        $dates = [
            $startedAt
        ];

        for ($i = 1; $i < $times; $i++) {
            $dates[] = (clone $dates[$i-1])->modify('1 months');
        }

        $this->createForSubscriptionConfiguration(
            $startedAt,
            $times,
            $interval
        )->shouldBeLike($dates);
    }
}
