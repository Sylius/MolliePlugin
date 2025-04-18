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

namespace SyliusMolliePlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

interface OrderInterface extends BaseOrderInterface
{
    public function isAbandonedEmail(): bool;

    public function setAbandonedEmail(bool $abandonedEmail): void;

    public function hasRecurringContents(): bool;

    public function hasNonRecurringContents(): bool;

    public function getRecurringSequenceIndex(): ?int;

    public function setRecurringSequenceIndex(int $recurringSequenceIndex): void;

    /** @return Collection|OrderItemInterface[] */
    public function getRecurringItems(): Collection;

    /** @return Collection|OrderItemInterface[] */
    public function getNonRecurringItems(): Collection;

    /**
     * @return string|null
     */
    public function getQrCode(): ?string;

    /**
     * @param string|null $qrCode
     *
     * @return void
     */
    public function setQrCode(?string $qrCode): void;

    /**
     * @return string|null
     */
    public function getMolliePaymentId(): ?string;

    /**
     * @param string|null $molliePaymentId
     *
     * @return void
     */
    public function setMolliePaymentId(?string $molliePaymentId): void;
}
