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

namespace Sylius\MolliePlugin\StateMachine;


trigger_deprecation(
    'sylius/mollie-plugin',
    '2.2',
    'The "%s" class is deprecated and will be removed in MolliePlugin 3.0',
    ShipmentTransitions::class,
);
final class ShipmentTransitions
{
    public const GRAPH = 'sylius_shipment';

    public const TRANSITION_CREATE_AND_SHIP = 'create_and_ship';

    private function __construct()
    {
    }
}
