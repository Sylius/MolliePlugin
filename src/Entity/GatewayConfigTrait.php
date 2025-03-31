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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** @mixin GatewayConfigInterface */
trait GatewayConfigTrait
{
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(
     *     targetEntity="SyliusMolliePlugin\Entity\MollieGatewayConfig",
     *     mappedBy="gateway",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     */
    #[ORM\OneToMany(
        mappedBy: "gateway",
        targetEntity: MollieGatewayConfig::class,
        cascade: ["all"],
        orphanRemoval: true
    )]
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
