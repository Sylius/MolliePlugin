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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\EventListener\Workflow\Shipment\ShipmentShipListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.listener.workflow.shipment.ship', ShipmentShipListener::class)
        ->args([service('sylius_mollie.shipping.mollie_shipment_notifier')])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_shipment.completed.ship']);
};
