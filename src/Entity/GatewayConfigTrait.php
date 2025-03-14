<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Doctrine\Common\Collections\Collection;

trait GatewayConfigTrait
{
    /** @var Collection|MollieGatewayConfigInterface[] */
    protected $mollieGatewayConfig;

    public function getMollieGatewayConfig(): ?Collection
    {
        return $this->mollieGatewayConfig;
    }

    public function setMollieGatewayConfig(?Collection $mollieGatewayConfig): void
    {
        $this->mollieGatewayConfig = $mollieGatewayConfig;
    }

    public function getMethodByName(string $methodName): ?MollieGatewayConfigInterface
    {
        $method = $this->mollieGatewayConfig->filter(function (MollieGatewayConfigInterface $mollieGatewayConfig) use ($methodName) {
            return $mollieGatewayConfig->getMethodId() === $methodName;
        });

        return $method->isEmpty() ? null : $method->first();
    }
}
