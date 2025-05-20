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

namespace Sylius\MolliePlugin\PartialShip\Converter;

use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;

trigger_deprecation(
    'sylius/mollie-plugin',
    '2.2',
    'The "%s" class is deprecated and will be removed in MolliePlugin 3.0',
    CreatePartialShipFromMollieInterface::class,
);
interface CreatePartialShipFromMollieInterface
{
    public function create(OrderInterface $order, Order $mollieOrder): OrderInterface;
}
