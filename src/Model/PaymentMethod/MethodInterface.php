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

namespace Sylius\MolliePlugin\Model\PaymentMethod;

use Sylius\MolliePlugin\Entity\ProductTypeInterface;
use Sylius\MolliePlugin\Model\DTO\MolliePayment\Amount;

/**
 * @phpstan-type ImageArray = array{
 *     svg: string,
 *     size1x?: string,
 *     size2x?: string,
 * }
 *
 * @phpstan-import-type AmountArray from Amount
 */
interface MethodInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function enable(): void;

    public function disable(): void;

    /** @return ImageArray */
    public function getImage(): array;

    /** @param ImageArray $image */
    public function setImage(array $image): void;

    /** @return AmountArray */
    public function getMinimumAmount(): array;

    /** @param AmountArray $minimumAmount */
    public function setMinimumAmount(array $minimumAmount): void;

    /** @return AmountArray */
    public function getMaximumAmount(): array;

    /** @param AmountArray $maximumAmount */
    public function setMaximumAmount(array $maximumAmount): void;

    public function getPaymentType(): string;

    public function setPaymentType(string $paymentType): void;

    /** @return string[] */
    public function getCountry(): array;

    /** @param string[] $country */
    public function setCountry(array $country): void;

    public function isCanRefunded(): bool;

    public function setCanRefunded(bool $canRefunded): void;

    /** @return array<string, mixed> */
    public function getIssuers(): array;

    /** @param array<string, mixed> $issuers */
    public function setIssuers(array $issuers): void;

    public function getDefaultCategory(): ?ProductTypeInterface;

    public function setDefaultCategory(?ProductTypeInterface $defaultCategory): void;

    public function isApplePayDirectButton(): ?bool;

    public function setApplePayDirectButton(?bool $applePayDirectButton): void;
}
