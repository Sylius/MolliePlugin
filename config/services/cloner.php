<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Cloner\AdjustmentCloner;
use Sylius\MolliePlugin\Cloner\AdjustmentClonerInterface;
use Sylius\MolliePlugin\Cloner\OrderItemCloner;
use Sylius\MolliePlugin\Cloner\OrderItemClonerInterface;
use Sylius\MolliePlugin\Cloner\ShipmentCloner;
use Sylius\MolliePlugin\Cloner\ShipmentClonerInterface;
use Sylius\MolliePlugin\Cloner\SubscriptionOrderCloner;
use Sylius\MolliePlugin\Cloner\SubscriptionOrderClonerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.cloner.order_item', OrderItemCloner::class)
        ->args([
            service('sylius.factory.order_item'),
            service('sylius.factory.order_item_unit'),
        ]);

    $services->alias(OrderItemClonerInterface::class, 'sylius_mollie.cloner.order_item');

    $services->set('sylius_mollie.cloner.adjustment', AdjustmentCloner::class)
        ->args([service('sylius.factory.adjustment')]);

    $services->alias(AdjustmentClonerInterface::class, 'sylius_mollie.cloner.adjustment');

    $services->set('sylius_mollie.cloner.shipment', ShipmentCloner::class)
        ->args([service('sylius.factory.shipment')]);

    $services->alias(ShipmentClonerInterface::class, 'sylius_mollie.cloner.shipment');

    $services->set('sylius_mollie.cloner.subscription_order', SubscriptionOrderCloner::class)
        ->args([
            service('sylius_mollie.cloner.order_item'),
            service('sylius.factory.order'),
            service('sylius.random_generator'),
            service('sylius_mollie.cloner.adjustment'),
            service('sylius_mollie.cloner.shipment'),
        ]);

    $services->alias(SubscriptionOrderClonerInterface::class, 'sylius_mollie.cloner.subscription_order');
};
