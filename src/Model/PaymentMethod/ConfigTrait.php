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
 * @mixin MethodInterface
 *
 * @phpstan-import-type AmountArray from Amount
 * @phpstan-import-type ImageArray from MethodInterface
 */
trait ConfigTrait
{
    /** @var ImageArray */
    protected array $image;

    /** @var AmountArray */
    protected array $minimumAmount;

    /** @var AmountArray */
    protected array $maximumAmount;

    protected string $paymentType;

    /** @var string[] */
    protected array $country;

    protected bool $canRefunded = true;

    /** @var array<string, mixed> */
    protected array $issuers = [];

    protected ?ProductTypeInterface $defaultCategory;

    protected ?bool $applePayDirectButton;

    public function getImage(): array
    {
        return $this->image;
    }

    public function setImage(array $image): void
    {
        $this->image = $image;
    }

    public function getMinimumAmount(): array
    {
        return $this->minimumAmount;
    }

    public function setMinimumAmount(array $minimumAmount): void
    {
        $this->minimumAmount = $minimumAmount;
    }

    public function getMaximumAmount(): array
    {
        return $this->maximumAmount;
    }

    public function setMaximumAmount(array $maximumAmount): void
    {
        $this->maximumAmount = $maximumAmount;
    }

    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    public function getCountry(): array
    {
        return $this->country;
    }

    public function setCountry(array $country): void
    {
        $this->country = $country;
    }

    public function isCanRefunded(): bool
    {
        return $this->canRefunded;
    }

    public function setCanRefunded(bool $canRefunded): void
    {
        $this->canRefunded = $canRefunded;
    }

    public function getIssuers(): array
    {
        return $this->issuers;
    }

    public function setIssuers(array $issuers): void
    {
        $this->issuers = $issuers;
    }

    public function getDefaultCategory(): ?ProductTypeInterface
    {
        return $this->defaultCategory;
    }

    public function setDefaultCategory(?ProductTypeInterface $defaultCategory): void
    {
        $this->defaultCategory = $defaultCategory;
    }

    public function isApplePayDirectButton(): ?bool
    {
        return $this->applePayDirectButton;
    }

    public function setApplePayDirectButton(?bool $applePayDirectButton): void
    {
        $this->applePayDirectButton = $applePayDirectButton;
    }
}
