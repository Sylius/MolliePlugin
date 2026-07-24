<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledChecker;
use Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledCheckerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.apple_pay.checker.apple_pay_enabled', ApplePayEnabledChecker::class)
        ->public()
        ->args([
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('sylius.payment_methods_resolver.default')->nullOnInvalid(),
        ]);

    $services->alias(ApplePayEnabledCheckerInterface::class, 'sylius_mollie.apple_pay.checker.apple_pay_enabled');
};
