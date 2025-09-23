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

namespace Sylius\MolliePlugin\Provider\Methods;

use Mollie\Api\Resources\Method;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\MollieMethodsException;

interface MollieMethodsProviderInterface
{
    /**
     * @return Method[]
     *
     * @throws MollieMethodsException
     */
    public function getMethods(GatewayConfigInterface $gateway): array;
}
