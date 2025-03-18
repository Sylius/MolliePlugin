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
        $method = $this->mollieGatewayConfig->filter(fn(MollieGatewayConfigInterface $mollieGatewayConfig) => $mollieGatewayConfig->getMethodId() === $methodName);

        return $method->isEmpty() ? null : $method->first();
    }
}
