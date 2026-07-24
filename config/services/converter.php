<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Converter\IntToStringConverter;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Converter\OrderConverter;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\MolliePlugin\Converter\PriceToAmountConverter;
use Sylius\MolliePlugin\Converter\PriceToAmountConverterInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.converter.int_to_string', IntToStringConverter::class)
        ->args([service('sylius_mollie.provider.divisor')]);

    $services->alias(IntToStringConverterInterface::class, 'sylius_mollie.converter.int_to_string');

    $services->set('sylius_mollie.converter.order', OrderConverter::class)
        ->args([
            service('sylius_mollie.converter.int_to_string'),
            service('sylius_mollie.calculator.calculate_tax_amount'),
            service('sylius_mollie.resolver.meal_voucher'),
            service('sylius.resolver.tax_rate'),
            service('sylius.matcher.zone'),
            service('request_stack'),
        ]);

    $services->alias(OrderConverterInterface::class, 'sylius_mollie.converter.order');

    $services->set('sylius_mollie.converter.price_to_amount', PriceToAmountConverter::class)
        ->args([
            service('sylius.context.currency'),
            service('sylius.context.locale'),
            service('sylius.formatter.money'),
        ]);

    $services->alias(PriceToAmountConverterInterface::class, 'sylius_mollie.converter.price_to_amount');
};
