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

class PaymentSurchargeFee implements ResourceInterface, PaymentSurchargeFeeInterface
{
    protected ?int $id = null;

    protected ?string $type = null;

    protected ?float $fixedAmount = null;

    protected ?float $percentage = null;

    protected ?float $surchargeLimit = null;

    protected ?MollieGatewayConfigInterface $mollieGatewayConfig = null;

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
