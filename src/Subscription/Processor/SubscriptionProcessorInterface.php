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

namespace Sylius\MolliePlugin\Subscription\Processor;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionProcessorInterface
{
    public function processNextPayment(MollieSubscriptionInterface $subscription): void;

    public function processNextSubscriptionPayment(MollieSubscriptionInterface $subscription): void;
}
