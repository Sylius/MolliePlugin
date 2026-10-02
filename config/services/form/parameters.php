<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $parameters = $container->parameters();
    $parameters->set('sylius_mollie.form.type.mollie_gateway_config.validation_groups', ['sylius']);
    $parameters->set('sylius_mollie.form.type.payment_methods.payment_surcharge_fee.validation_groups', ['sylius']);
    $parameters->set('sylius_mollie.form.type.mollie.validation_groups', ['sylius']);
};
