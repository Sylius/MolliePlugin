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

namespace SyliusMolliePlugin\Factory;

use Sylius\Component\Resource\Factory\FactoryInterface;
use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use SyliusMolliePlugin\Payments\Methods\MethodInterface;

interface MollieGatewayConfigFactoryInterface extends FactoryInterface
{
    public function create(
        MethodInterface $method,
        GatewayConfigInterface $gateway,
        int $key
    ): MollieGatewayConfigInterface;
}
