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
