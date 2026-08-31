<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\ApplePay\Resolver\AddressResolver;
use Sylius\MolliePlugin\ApplePay\Resolver\AddressResolverInterface;
use Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectApiOrderPaymentResolver;
use Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectApiOrderPaymentResolverInterface;
use Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectApiPaymentResolver;
use Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectApiPaymentResolverInterface;
use Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectPaymentTypeResolver;
use Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectPaymentTypeResolverInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.apple_pay.resolver.address', AddressResolver::class)
        ->args([
            service('sylius_mollie.apple_pay.validator.apple_pay_address'),
            service('sylius.repository.customer'),
            service('sylius.custom_factory.address'),
            service('sylius.factory.customer'),
        ]);

    $services->alias(AddressResolverInterface::class, 'sylius_mollie.apple_pay.resolver.address');

    $services->set('sylius_mollie.apple_pay.resolver.apple_pay_direct_api_order_payment', ApplePayDirectApiOrderPaymentResolver::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius_mollie.resolver.mollie_api_client_key'),
            service('sylius_mollie.converter.order'),
            service('sylius_mollie.apple_pay.provider.order_payment_apple_pay_direct'),
            service('sylius_mollie.resolver.payment_locale'),
            service('sylius_mollie.provider.divisor'),
        ]);

    $services->alias(ApplePayDirectApiOrderPaymentResolverInterface::class, 'sylius_mollie.apple_pay.resolver.apple_pay_direct_api_order_payment');

    $services->set('sylius_mollie.apple_pay.resolver.apple_pay_direct_api_payment', ApplePayDirectApiPaymentResolver::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius_mollie.resolver.mollie_api_client_key'),
            service('sylius_mollie.apple_pay.provider.order_payment_apple_pay_direct'),
        ]);

    $services->alias(ApplePayDirectApiPaymentResolverInterface::class, 'sylius_mollie.apple_pay.resolver.apple_pay_direct_api_payment');

    $services->set('sylius_mollie.apple_pay.resolver.apple_pay_direct_payment_type', ApplePayDirectPaymentTypeResolver::class)
        ->args([
            service('sylius_mollie.apple_pay.resolver.apple_pay_direct_api_payment'),
            service('sylius_mollie.apple_pay.resolver.apple_pay_direct_api_order_payment'),
            service('sylius_mollie.converter.int_to_string'),
        ]);

    $services->alias(ApplePayDirectPaymentTypeResolverInterface::class, 'sylius_mollie.apple_pay.resolver.apple_pay_direct_payment_type');
};
