<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\EventListener\CheckoutOrderCollidingProductsListener;
use Sylius\MolliePlugin\EventListener\PaymentMethodsRefreshListener;
use Sylius\MolliePlugin\EventListener\PaymentMethodUploadLogoListener;
use Sylius\MolliePlugin\EventListener\ShipmentShipEventListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.listener.shipment_ship', ShipmentShipEventListener::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('request_stack'),
        ])
        ->tag('kernel.event_listener', ['event' => 'sylius.shipment.post_ship', 'method' => 'shipAll']);

    $services->set('sylius_mollie.listener.payment_method_logo_upload', PaymentMethodUploadLogoListener::class)
        ->args([
            service('sylius.manager.gateway_config'),
            service('sylius_mollie.uploader.payment_method_logo'),
        ])
        ->tag('kernel.event_listener', ['event' => 'sylius.payment_method.pre_create', 'method' => 'uploadLogo'])
        ->tag('kernel.event_listener', ['event' => 'sylius.payment_method.pre_update', 'method' => 'uploadLogo']);

    $services->set('sylius_mollie.listener.checkout_order_colliding_products', CheckoutOrderCollidingProductsListener::class)
        ->args([
            service('router'),
            service('translator'),
            service('request_stack'),
        ])
        ->tag('kernel.event_listener', ['event' => 'sylius.order.initialize_address', 'method' => 'onUpdate']);

    $services->set('sylius_mollie.listener.payment_methods_refresh', PaymentMethodsRefreshListener::class)
        ->args([
            service('sylius_mollie.updater.mollie_methods'),
            service('request_stack'),
        ])
        ->tag('kernel.event_listener', ['event' => 'sylius.payment_method.initialize_update', 'method' => 'refreshMethods']);
};
