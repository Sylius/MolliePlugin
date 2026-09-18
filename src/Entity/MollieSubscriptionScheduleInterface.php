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

use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @deprecated since Mollie 3.3 and will be removed in 4.0.
 *
 * @see https://github.com/Sylius/MolliePlugin/blob/3.3/UPGRADE-3.3.md for migration details
 */
interface MollieSubscriptionScheduleInterface extends ResourceInterface
{
    public function getMollieSubscription(): MollieSubscriptionInterface;

    public function getScheduledDate(): \DateTime;

    public function getFulfilledDate(): ?\DateTimeInterface;

    public function setMollieSubscription(MollieSubscriptionInterface $mollieSubscription): void;

    public function setScheduledDate(\DateTime $scheduledDate): void;

    public function setFulfilledDate(?\DateTimeInterface $fulfilledDate): void;

    public function isFulfilled(): bool;

    public function setScheduleIndex(int $scheduleIndex): void;

    public function getScheduleIndex(): int;
}
