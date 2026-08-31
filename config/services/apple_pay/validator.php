<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\ApplePay\Validator\ApplePayAddressValidator;
use Sylius\MolliePlugin\ApplePay\Validator\ApplePayAddressValidatorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.apple_pay.validator.apple_pay_address', ApplePayAddressValidator::class);

    $services->alias(ApplePayAddressValidatorInterface::class, 'sylius_mollie.apple_pay.validator.apple_pay_address');
};
