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

namespace Sylius\MolliePlugin\Resolver\PartialShip;

use Doctrine\Common\Collections\Collection;
use Mollie\Api\Resources\Order;
use Sylius\MolliePlugin\DTO\PartialShipItems;

interface FromSyliusToMollieLinesResolverInterface
{
    public function resolve(Collection $units, Order $mollieOrder): PartialShipItems;
}
