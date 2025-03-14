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

namespace SyliusMolliePlugin\Payments\Methods;

use SyliusMolliePlugin\Entity\ProductTypeInterface;

trait ConfigTrait
{
    /** @var array */
    protected $image;

    /** @var array */
    protected $minimumAmount;

    /** @var array */
    protected $maximumAmount;

    /** @var string */
    protected $paymentType;

    /** @var array */
    protected $country;

    /** @var bool */
    protected $canRefunded = true;

    /** @var array */
    protected $issuers = [];

    /** @var ProductTypeInterface|null */
    protected $defaultCategory;

    /** @var bool|null */
    protected $applePayDirectButton;

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
