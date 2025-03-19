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

namespace Sylius\MolliePlugin\Payments\Methods;

use Sylius\MolliePlugin\Entity\ProductTypeInterface;

interface MethodInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function enable(): void;

    public function disable(): void;

    public function getImage(): array;

    public function setImage(array $image): void;

    public function getMinimumAmount(): array;

    public function setMinimumAmount(array $minimumAmount): void;

    public function getMaximumAmount(): array;

    public function setMaximumAmount(array $maximumAmount): void;

    public function getPaymentType(): string;

    public function setPaymentType(string $paymentType): void;

    public function getCountry(): array;

    public function setCountry(array $country): void;

    public function isCanRefunded(): bool;

    public function setCanRefunded(bool $canRefunded): void;

    public function getIssuers(): array;

    public function setIssuers(array $issuers): void;

    public function getDefaultCategory(): ?ProductTypeInterface;

    public function setDefaultCategory(?ProductTypeInterface $defaultCategory): void;

    public function isApplePayDirectButton(): ?bool;

    public function setApplePayDirectButton(?bool $applePayDirectButton): void;
}
