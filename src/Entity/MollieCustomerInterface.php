<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

interface MollieCustomerInterface
{
    public function getId(): int;

    public function getProfileId(): ?string;

    public function setProfileId(string $profileId): void;

    public function getEmail(): string;

    public function setEmail(string $email): void;

    public function isCreditCardSaved(): ?string;

    public function setIsCreditCardSaved(?string $isCreditCardSaved): void;
}
