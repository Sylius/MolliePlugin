<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

class PaymentSurchargeFee implements ResourceInterface, PaymentSurchargeFeeInterface
{
    /** @var int|null */
    protected $id;

    /** @var string|null */
    protected $type;

    /** @var float|null */
    protected $fixedAmount;

    /** @var float|null */
    protected $percentage;

    /** @var float|null */
    protected $surchargeLimit;

    /** @var MollieGatewayConfigInterface|null */
    protected $mollieGatewayConfig;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getFixedAmount(): ?float
    {
        return $this->fixedAmount;
    }

    public function setFixedAmount(?float $fixedAmount): void
    {
        $this->fixedAmount = $fixedAmount;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function setPercentage(?float $percentage): void
    {
        $this->percentage = $percentage;
    }

    public function getSurchargeLimit(): ?float
    {
        return $this->surchargeLimit;
    }

    public function setSurchargeLimit(?float $surchargeLimit): void
    {
        $this->surchargeLimit = $surchargeLimit;
    }

    public function getMollieGatewayConfig(): ?MollieGatewayConfigInterface
    {
        return $this->mollieGatewayConfig;
    }

    public function setMollieGatewayConfig(?MollieGatewayConfigInterface $mollieGatewayConfig): void
    {
        $this->mollieGatewayConfig = $mollieGatewayConfig;
    }
}
