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
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface as BaseGatewayConfigInterface;

interface GatewayConfigInterface extends BaseGatewayConfigInterface
{
    public function getMollieGatewayConfig(): ?Collection;

    public function setMollieGatewayConfig(?Collection $mollieGatewayConfig): void;

    public function getMethodByName(string $methodName): ?MollieGatewayConfigInterface;
}
