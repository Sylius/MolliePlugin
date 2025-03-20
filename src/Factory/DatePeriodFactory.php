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

namespace Sylius\MolliePlugin\Factory;

use Webmozart\Assert\Assert;

final class DatePeriodFactory implements DatePeriodFactoryInterface
{
    public const READ_PATTERN = '/^(?<intervalValue>\d{1,})\s(?<intervalStep>months|weeks|days)$/';

    public function createForSubscriptionConfiguration(
        \DateTime $start,
        int $times,
        string $interval,
    ): array {
        Assert::regex($interval, self::READ_PATTERN);

        $dates = [
            $start,
        ];
        for ($i = 1; $i < $times; ++$i) {
            $dates[] = (clone $dates[$i - 1])->modify(sprintf('+%s', $interval));
        }

        return $dates;
    }
}
