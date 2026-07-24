<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectPaymentProvider;
use Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectPaymentProviderInterface;
use Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectProvider;
use Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectProviderInterface;
use Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProvider;
use Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProviderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.apple_pay.provider.apple_pay_direct', ApplePayDirectProvider::class)
        ->args([
            service('sylius_mollie.apple_pay.resolver.address'),
            service('sylius_mollie.apple_pay.provider.order_payment_apple_pay_direct'),
            service('sylius_mollie.provider.customer'),
            service('sylius_mollie.apple_pay.provider.apple_pay_direct_payment'),
        ]);

    $services->alias(ApplePayDirectProviderInterface::class, 'sylius_mollie.apple_pay.provider.apple_pay_direct');

    $services->set('sylius_mollie.apple_pay.provider.order_payment_apple_pay_direct', OrderPaymentApplePayDirectProvider::class)
        ->args([
            service('sylius.custom_factory.payment'),
            service('sylius_abstraction.state_machine'),
            service('sylius.repository.payment_method'),
            service('sylius.repository.gateway_config'),
            service('sylius_mollie.payum.provider.payment_token'),
            service('payum'),
        ]);

    $services->alias(OrderPaymentApplePayDirectProviderInterface::class, 'sylius_mollie.apple_pay.provider.order_payment_apple_pay_direct');

    $services->set('sylius_mollie.apple_pay.provider.apple_pay_direct_payment', ApplePayDirectPaymentProvider::class)
        ->args([service('sylius_mollie.apple_pay.resolver.apple_pay_direct_payment_type')]);

    $services->alias(ApplePayDirectPaymentProviderInterface::class, 'sylius_mollie.apple_pay.provider.apple_pay_direct_payment');
};
