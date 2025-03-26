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

namespace Sylius\MolliePlugin\Entity;

/** @mixin OrderInterface */
trait RecurringOrderTrait
{
    protected ?int $recurringSequenceIndex = null;

    protected ?MollieSubscriptionInterface $subscription = null;

    public function getRecurringSequenceIndex(): ?int
    {
        return $this->recurringSequenceIndex;
    }

    public function setRecurringSequenceIndex(?int $recurringSequenceIndex): void
    {
        $this->recurringSequenceIndex = $recurringSequenceIndex;
    }

    public function getSubscription(): ?MollieSubscriptionInterface
    {
        return $this->subscription;
    }

    public function setSubscription(?MollieSubscriptionInterface $subscription): void
    {
        $this->subscription = $subscription;
    }
}
