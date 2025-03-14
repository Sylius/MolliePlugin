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

use SyliusMolliePlugin\Payments\Methods\MethodInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;

interface MollieGatewayConfigInterface extends MethodInterface, TranslatableInterface
{
    public const ALL_COUNTRIES = 'ALL_COUNTRIES';

    public const SELECTED_COUNTRIES = 'SELECTED_COUNTRIES';

    public function getId(): int;

    public function getMethodId(): ?string;

    public function setMethodId(string $methodId): void;

    public function getGateway(): GatewayConfigInterface;

    public function setGateway(GatewayConfigInterface $gateway): void;

    public function getPaymentSurchargeFee(): ?PaymentSurchargeFeeInterface;

    public function setPaymentSurchargeFee(?PaymentSurchargeFeeInterface $paymentSurchargeFee): void;

    public function getCustomizeMethodImage(): ?MollieMethodImageInterface;

    public function setCustomizeMethodImage(?MollieMethodImageInterface $customizeMethodImage): void;

    public function getCountryLevel(): ?array;

    public function setCountryLevel(?array $countryLevel): void;

    public function getOrderExpiration(): ?int;

    public function setOrderExpiration(?int $orderExpiration): void;

    public function getPaymentDescription(): ?string;

    public function setPaymentDescription(?string $paymentDescription): void;

    public function isLoggerEnabled(): ?bool;

    public function setLoggerEnabled(?bool $loggerEnabled): void;

    public function getLoggerLevel(): ?int;

    public function setLoggerLevel(?int $loggerLevel): void;

    public function getCountryLevelAllowed(): ?array;

    public function setCountryLevelAllowed(?array $countryLevelAllowed): void;

    public function getCountryLevelExcluded(): ?array;

    public function setCountryLevelExcluded(?array $countryLevelExcluded): void;

    public function getCountryRestriction(): ?string;

    public function setCountryRestriction(?string $countryRestriction): void;

    public function getPosition(): ?int;

    public function setPosition(?int $position): void;

    public function getAmountLimits(): ?MollieMinMaxInterface;

    public function setAmountLimits(?MollieMinMaxInterface $amountLimits): void;

    public function hasTranslationLocale(string $localeCode): bool;

    public function isQrCodeEnabled(): ?bool;

    public function setQrCodeEnabled(?bool $qrCodeEnabled): void;
}
