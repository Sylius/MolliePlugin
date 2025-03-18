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

namespace SyliusMolliePlugin\Guard;

use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionGuardInterface
{
    public function isCompletable(MollieSubscriptionInterface $subscription): bool;
}
