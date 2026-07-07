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

namespace Sylius\MolliePlugin\Shipping;

use Mollie\Api\Exceptions\ApiException;
use Sylius\Component\Core\Model\ShipmentInterface;

interface MollieShipmentNotifierInterface
{
    /**
     * @throws ApiException
     */
    public function shipAll(ShipmentInterface $shipment): void;
}
