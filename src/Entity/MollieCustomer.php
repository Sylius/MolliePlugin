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

class MollieCustomer implements ResourceInterface, MollieCustomerInterface
{
    protected int $id;

    protected ?string $profileId = null;

    protected string $email;

    protected ?string $isCreditCardSaved = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProfileId(): ?string
    {
        return $this->profileId;
    }

    public function setProfileId(string $profileId): void
    {
        $this->profileId = $profileId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function isCreditCardSaved(): ?string
    {
        return $this->isCreditCardSaved;
    }

    public function setIsCreditCardSaved(?string $isCreditCardSaved): void
    {
        $this->isCreditCardSaved = $isCreditCardSaved;
    }
}
