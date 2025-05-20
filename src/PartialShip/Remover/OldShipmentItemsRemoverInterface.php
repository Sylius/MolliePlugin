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

namespace Sylius\MolliePlugin\PartialShip\Remover;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MolliePlugin\Model\DTO\PartialShipItems;

trigger_deprecation(
    'sylius/mollie-plugin',
    '2.2',
    'The "%s" class is deprecated and will be removed in MolliePlugin 3.0',
    OldShipmentItemsRemoverInterface::class,
);
interface OldShipmentItemsRemoverInterface
{
    public function remove(OrderInterface $order, PartialShipItems $shipItems): OrderInterface;
}
