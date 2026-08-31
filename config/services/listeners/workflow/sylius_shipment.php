<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\EventListener\Workflow\Shipment\ShipmentShipListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.listener.workflow.shipment.ship', ShipmentShipListener::class)
        ->args([service('sylius_mollie.shipping.mollie_shipment_notifier')])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_shipment.completed.ship']);
};
