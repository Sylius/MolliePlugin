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
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\AbstractMethod;

class MollieGatewayConfig extends AbstractMethod implements ResourceInterface, MollieGatewayConfigInterface, \Stringable
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    protected int $id;

    protected ?string $methodId = null;

    protected GatewayConfigInterface $gateway;

    protected ?PaymentSurchargeFeeInterface $paymentSurchargeFee = null;

    protected ?MollieMinMaxInterface $amountLimits = null;

    protected ?MollieMethodImageInterface $customizeMethodImage = null;

    /** @var array<array-key, mixed>|null */
    protected ?array $countryLevel = null;

    /** @var array<array-key, mixed>|null */
    protected ?array $countryLevelAllowed = null;

    /** @var array<array-key, mixed>|null */
    protected ?array $countryLevelExcluded = null;

    protected ?int $orderExpirationDays = 28;

    protected ?string $paymentDescription = null;

    protected ?bool $loggerEnabled = null;

    protected ?int $loggerLevel = null;

    protected ?string $countryRestriction = null;

    protected ?int $position = null;

    protected ?bool $qrCodeEnabled = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMethodId(): ?string
    {
        return $this->methodId;
    }

    public function setMethodId(?string $methodId): void
    {
        $this->methodId = $methodId;
    }

    public function getGateway(): GatewayConfigInterface
    {
        return $this->gateway;
    }

    public function setGateway(GatewayConfigInterface $gateway): void
    {
        $this->gateway = $gateway;
    }

    public function getPaymentSurchargeFee(): ?PaymentSurchargeFeeInterface
    {
        return $this->paymentSurchargeFee;
    }

    public function setPaymentSurchargeFee(?PaymentSurchargeFeeInterface $paymentSurchargeFee): void
    {
        $this->paymentSurchargeFee = $paymentSurchargeFee;
    }

    public function getCustomizeMethodImage(): ?MollieMethodImageInterface
    {
        return $this->customizeMethodImage;
    }

    public function setCustomizeMethodImage(?MollieMethodImageInterface $customizeMethodImage): void
    {
        $this->customizeMethodImage = $customizeMethodImage;
    }

    public function getCountryLevel(): ?array
    {
        return $this->countryLevel;
    }

    public function setCountryLevel(?array $countryLevel): void
    {
        $this->countryLevel = $countryLevel;
    }

    public function getOrderExpirationDays(): ?int
    {
        return $this->orderExpirationDays;
    }

    public function setOrderExpirationDays(?int $orderExpirationDays): void
    {
        $this->orderExpirationDays = $orderExpirationDays;
    }

    public function getPaymentDescription(): ?string
    {
        return $this->paymentDescription;
    }

    public function setPaymentDescription(?string $paymentDescription): void
    {
        $this->paymentDescription = $paymentDescription;
    }

    public function isLoggerEnabled(): ?bool
    {
        return $this->loggerEnabled;
    }

    public function setLoggerEnabled(?bool $loggerEnabled): void
    {
        $this->loggerEnabled = $loggerEnabled;
    }

    public function getLoggerLevel(): ?int
    {
        return $this->loggerLevel;
    }

    public function setLoggerLevel(?int $loggerLevel): void
    {
        $this->loggerLevel = $loggerLevel;
    }

    public function __toString(): string
    {
        return \sprintf('%s', $this->name);
    }

    public function getCountryLevelAllowed(): ?array
    {
        return $this->countryLevelAllowed;
    }

    public function setCountryLevelAllowed(?array $countryLevelAllowed): void
    {
        $this->countryLevelAllowed = $countryLevelAllowed;
    }

    public function getCountryLevelExcluded(): ?array
    {
        return $this->countryLevelExcluded;
    }

    public function setCountryLevelExcluded(?array $countryLevelExcluded): void
    {
        $this->countryLevelExcluded = $countryLevelExcluded;
    }

    public function getCountryRestriction(): ?string
    {
        return $this->countryRestriction;
    }

    public function setCountryRestriction(?string $countryRestriction): void
    {
        $this->countryRestriction = $countryRestriction;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getAmountLimits(): ?MollieMinMaxInterface
    {
        return $this->amountLimits;
    }

    public function setAmountLimits(?MollieMinMaxInterface $amountLimits): void
    {
        $this->amountLimits = $amountLimits;
    }

    public function isQrCodeEnabled(): ?bool
    {
        return $this->qrCodeEnabled;
    }

    public function setQrCodeEnabled(?bool $qrCodeEnabled): void
    {
        $this->qrCodeEnabled = $qrCodeEnabled;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new MollieGatewayConfigTranslation();
    }

    public function getTranslation(?string $locale = null): MollieGatewayConfigTranslationInterface
    {
        /** @var MollieGatewayConfigTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    public function hasTranslationLocale(string $localeCode): bool
    {
        return $this->translations->containsKey($localeCode);
    }
}
