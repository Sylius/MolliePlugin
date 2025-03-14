<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

class MollieMinMax implements ResourceInterface, MollieMinMaxInterface
{
    /** @var int|null */
    protected $id;

    /** @var float|null */
    protected $minimumAmount;

    /** @var float|null */
    protected $maximumAmount;

    /** @var MollieGatewayConfigInterface|null */
    protected $mollieGatewayConfig;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinimumAmount(): ?float
    {
        return $this->minimumAmount;
    }

    public function setMinimumAmount(?float $minimumAmount): void
    {
        $this->minimumAmount = $minimumAmount;
    }

    public function getMaximumAmount(): ?float
    {
        return $this->maximumAmount;
    }

    public function setMaximumAmount(?float $maximumAmount): void
    {
        $this->maximumAmount = $maximumAmount;
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
