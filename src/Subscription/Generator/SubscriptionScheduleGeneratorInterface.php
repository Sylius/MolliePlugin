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

namespace Sylius\MolliePlugin\Subscription\Generator;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionScheduleInterface;

interface SubscriptionScheduleGeneratorInterface
{
    /** @return array<array-key, MollieSubscriptionScheduleInterface> */
    public function generate(MollieSubscriptionInterface $subscription): array;
}
