<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Refund\Converter\RefundDataConverter;
use Sylius\MolliePlugin\Refund\Converter\RefundDataConverterInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.refund.converter.refund_data', RefundDataConverter::class)
        ->args([service('sylius_mollie.converter.int_to_string')]);

    $services->alias(RefundDataConverterInterface::class, 'sylius_mollie.refund.converter.refund_data');
};
