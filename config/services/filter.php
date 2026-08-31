<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Filter\MollieMethodFilter;
use Sylius\MolliePlugin\Filter\MollieMethodFilterInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.filter.mollie_method', MollieMethodFilter::class);

    $services->alias(MollieMethodFilterInterface::class, 'sylius_mollie.filter.mollie_method');
};
