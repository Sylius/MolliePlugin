<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Validator\Constraints\PaymentMethodCheckoutValidator;
use Sylius\MolliePlugin\Validator\Constraints\PaymentMethodMollieChannelUniqueValidator;
use Sylius\MolliePlugin\Validator\Constraints\PaymentSurchargeTypeValidator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.validator.payment_surcharge_type', PaymentSurchargeTypeValidator::class)
        ->tag('validator.constraint_validator', ['alias' => 'channels']);

    $services->set('sylius_mollie.validator.apple_pay_direct.payment_method_checkout', PaymentMethodCheckoutValidator::class)
        ->args([
            service('sylius_mollie.resolver.order.payment_checkout_order'),
            service('request_stack'),
            service('sylius_mollie.payum.checker.mollie_gateway_factory'),
        ])
        ->tag('validator.constraint_validator');

    $services->set('sylius_mollie.validator.payment_method_mollie_channel_unique', PaymentMethodMollieChannelUniqueValidator::class)
        ->args([
            service('sylius_mollie.repository.query.payment_method.mollie_based'),
            service('translator'),
        ])
        ->tag('validator.constraint_validator');
};
