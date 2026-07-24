<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Updater\MollieMethodsUpdater;
use Sylius\MolliePlugin\Updater\MollieMethodsUpdaterInterface;
use Sylius\MolliePlugin\Updater\MolliePaymentMethodPositionUpdater;
use Sylius\MolliePlugin\Updater\MolliePaymentMethodPositionUpdaterInterface;
use Sylius\MolliePlugin\Updater\MolliePaymentMethodsSynchronizer;
use Sylius\MolliePlugin\Updater\MolliePaymentMethodsSynchronizerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius_mollie.mollie_payment_methods_refresh_ttl', 7200);

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.updater.mollie_payment_method_position', MolliePaymentMethodPositionUpdater::class)
        ->args([
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('doctrine.orm.entity_manager'),
        ]);

    $services->alias(MolliePaymentMethodPositionUpdaterInterface::class, 'sylius_mollie.updater.mollie_payment_method_position');

    $services->set('sylius_mollie.updater.mollie_methods', MollieMethodsUpdater::class)
        ->args([
            service('cache.app'),
            service('sylius_mollie.provider.methods.mollie_methods'),
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('sylius_mollie.factory.mollie_gateway_config'),
            service('sylius_mollie.factory.methods'),
            service('doctrine.orm.default_entity_manager'),
            '%sylius_mollie.mollie_payment_methods_refresh_ttl%',
        ]);

    $services->alias(MollieMethodsUpdaterInterface::class, 'sylius_mollie.updater.mollie_methods');

    $services->set('sylius_mollie.synchronizer.mollie_payment_methods', MolliePaymentMethodsSynchronizer::class)
        ->args([
            service('sylius.repository.payment_method'),
            service('sylius.context.channel'),
            service('sylius_mollie.updater.mollie_methods'),
        ]);

    $services->alias(MolliePaymentMethodsSynchronizerInterface::class, 'sylius_mollie.synchronizer.mollie_payment_methods');
};
