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
use Doctrine\ORM\Mapping as ORM;

/** @mixin GatewayConfigInterface */
trait GatewayConfigTrait
{
    /**
     * @var Collection<MollieGatewayConfigInterface>
     *
     * @ORM\OneToMany(
     *     targetEntity="Sylius\MolliePlugin\Entity\MollieGatewayConfig",
     *     mappedBy="gateway",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     */
    #[ORM\OneToMany(
        mappedBy: 'gateway',
        targetEntity: MollieGatewayConfig::class,
        cascade: ['all'],
        orphanRemoval: true,
    )]
    protected Collection $mollieGatewayConfig;

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
        $method = $this->mollieGatewayConfig->filter(fn (MollieGatewayConfigInterface $mollieGatewayConfig) => $mollieGatewayConfig->getMethodId() === $methodName);

        return $method->isEmpty() ? null : $method->first();
    }
}
