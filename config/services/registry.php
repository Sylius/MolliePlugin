<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Registry\PaymentMethodRegistry;
use Sylius\MolliePlugin\Registry\PaymentMethodRegistryInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.registry.payment_method', PaymentMethodRegistry::class)
        ->public();

    $services->alias(PaymentMethodRegistryInterface::class, 'sylius_mollie.registry.payment_method');
};
