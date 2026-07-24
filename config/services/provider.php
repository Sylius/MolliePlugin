<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Provider\CustomerProvider;
use Sylius\MolliePlugin\Provider\CustomerProviderInterface;
use Sylius\MolliePlugin\Provider\DivisorProvider;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\Methods\MollieMethodsProvider;
use Sylius\MolliePlugin\Provider\Methods\MollieMethodsProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProvider;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.provider.divisor', DivisorProvider::class);

    $services->alias(DivisorProviderInterface::class, 'sylius_mollie.provider.divisor');

    $services->set('sylius_mollie.provider.customer', CustomerProvider::class)
        ->args([
            service('sylius.repository.customer'),
            service('sylius.factory.customer'),
        ]);

    $services->alias(CustomerProviderInterface::class, 'sylius_mollie.provider.customer');

    $services->set('sylius_mollie.provider.payment_description', PaymentDescriptionProvider::class)
        ->args([service('sylius_payum.provider.payment_description')]);

    $services->alias(PaymentDescriptionProviderInterface::class, 'sylius_mollie.provider.payment_description');

    $services->set('sylius_mollie.provider.methods.mollie_methods', MollieMethodsProvider::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius_mollie.logger.mollie_logger_action'),
        ]);

    $services->alias(MollieMethodsProviderInterface::class, 'sylius_mollie.provider.methods.mollie_methods');
};
