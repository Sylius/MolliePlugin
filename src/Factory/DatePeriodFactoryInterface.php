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

interface DatePeriodFactoryInterface
{
    /** @return array<array-key, \DateTime> */
    public function createForSubscriptionConfiguration(
        \DateTime $start,
        int $times,
        string $interval,
    ): array;
}
