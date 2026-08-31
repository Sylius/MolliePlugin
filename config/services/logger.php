<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Logger\MollieLoggerAction;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.logger.mollie_logger_action', MollieLoggerAction::class)
        ->args([
            service('sylius_mollie.factory.mollie_logger'),
            service('sylius_mollie.repository.mollie_logger'),
            service('sylius.repository.gateway_config'),
            service('sylius_mollie.resolver.mollie_factory_name'),
        ]);

    $services->alias(MollieLoggerActionInterface::class, 'sylius_mollie.logger.mollie_logger_action');
};
