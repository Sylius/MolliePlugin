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

namespace Sylius\MolliePlugin\Factory;

use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\MethodInterface;

interface MollieGatewayConfigFactoryInterface extends FactoryInterface
{
    public function create(
        MethodInterface $method,
        GatewayConfigInterface $gateway,
        int $key,
    ): MollieGatewayConfigInterface;
}
