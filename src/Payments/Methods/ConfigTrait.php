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

trait ConfigTrait
{
    /**
     * @var array{
     *     svg: string,
     *     size1x?: string,
     *     size2x?: string
     * }
     */
    protected array $image;

    /** @var array{value: string, currency: string} */
    protected array $minimumAmount;

    /** @var array{value: string, currency: string} */
    protected array $maximumAmount;

    protected string $paymentType;

    /** @var string[] */
    protected array $country;

    protected bool $canRefunded = true;

    /** @var array<string, mixed> */
    protected array $issuers = [];

    protected ?ProductTypeInterface $defaultCategory;

    protected ?bool $applePayDirectButton;

    /**
     * @return array{
     *     svg: string,
     *     size1x?: string,
     *     size2x?: string
     * }
     */
    public function getImage(): array
    {
        return $this->image;
    }

    /**
     * @param array{
     *     svg: string,
     *     size1x?: string,
     *     size2x?: string
     * } $image
     */
    public function setImage(array $image): void
    {
        $this->image = $image;
    }

    /**
     * @return array{
     *     value: string,
     *     currency: string
     * }
     */
    public function getMinimumAmount(): array
    {
        return $this->minimumAmount;
    }

    /**
     * @param array{
     *     value: string,
     *     currency: string
     * } $minimumAmount
     */
    public function setMinimumAmount(array $minimumAmount): void
    {
        $this->minimumAmount = $minimumAmount;
    }

    /**
     * @return array{
     *     value: string,
     *     currency: string
     * }
     */
    public function getMaximumAmount(): array
    {
        return $this->maximumAmount;
    }

    /**
     * @param array{
     *     value: string,
     *     currency: string
     * } $maximumAmount
     */
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

    /** @return string[] */
    public function getCountry(): array
    {
        return $this->country;
    }

    /** @param string[] $country */
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

    /** @return array<string, mixed> */
    public function getIssuers(): array
    {
        return $this->issuers;
    }

    /** @param array<string, mixed> $issuers */
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
