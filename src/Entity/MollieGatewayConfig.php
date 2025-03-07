<?php


declare(strict_types=1);

namespace SyliusMolliePlugin\Entity;

use SyliusMolliePlugin\Payments\Methods\AbstractMethod;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

class MollieGatewayConfig extends AbstractMethod implements ResourceInterface, MollieGatewayConfigInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;

        getTranslation as private doGetTranslation;
    }

    /** @var int */
    protected $id;

    /** @var string|null */
    protected $methodId;

    /** @var GatewayConfigInterface */
    protected $gateway;

    /** @var PaymentSurchargeFeeInterface|null */
    protected $paymentSurchargeFee;

    /** @var MollieMinMaxInterface|null */
    protected $amountLimits;

    /** @var MollieMethodImageInterface|null */
    protected $customizeMethodImage;

    /** @var array|null */
    protected $countryLevel;

    /** @var array|null */
    protected $countryLevelAllowed;

    /** @var array|null */
    protected $countryLevelExcluded;

    /** @var int|null */
    protected $orderExpiration = 28;

    /** @var string|null */
    protected $paymentDescription;

    /** @var bool|null */
    protected $loggerEnabled;

    /** @var int|null */
    protected $loggerLevel;

    /** @var string|null */
    protected $countryRestriction;

    /** @var int|null */
    protected $position;

    /**
     * @var bool|null
     */
    protected $qrCodeEnabled;

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

    public function getOrderExpiration(): ?int
    {
        return $this->orderExpiration;
    }

    public function setOrderExpiration(?int $orderExpiration): void
    {
        $this->orderExpiration = $orderExpiration;
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
