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

interface MollieMinMaxInterface
{
    public function getId(): ?int;

    public function getMinimumAmount(): ?float;

    public function setMinimumAmount(?float $minimumAmount): void;

    public function getMaximumAmount(): ?float;

    public function setMaximumAmount(?float $maximumAmount): void;

    public function getMollieGatewayConfig(): ?MollieGatewayConfigInterface;

    public function setMollieGatewayConfig(?MollieGatewayConfigInterface $mollieGatewayConfig): void;
}
