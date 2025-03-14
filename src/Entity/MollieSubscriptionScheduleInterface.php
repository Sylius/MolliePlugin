<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

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
