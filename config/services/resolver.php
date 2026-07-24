<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Resolver\ApiKeysTestResolver;
use Sylius\MolliePlugin\Resolver\ApiKeysTestResolverInterface;
use Sylius\MolliePlugin\Resolver\MealVoucherResolver;
use Sylius\MolliePlugin\Resolver\MealVoucherResolverInterface;
use Sylius\MolliePlugin\Resolver\MollieAllowedMethodsResolver;
use Sylius\MolliePlugin\Resolver\MollieAllowedMethodsResolverInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolver;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Sylius\MolliePlugin\Resolver\MollieCountriesRestrictionResolver;
use Sylius\MolliePlugin\Resolver\MollieCountriesRestrictionResolverInterface;
use Sylius\MolliePlugin\Resolver\MollieFactoryNameResolver;
use Sylius\MolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolver;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;
use Sylius\MolliePlugin\Resolver\MolliePaymentMethodImageResolver;
use Sylius\MolliePlugin\Resolver\MolliePaymentMethodImageResolverInterface;
use Sylius\MolliePlugin\Resolver\MolliePaymentsMethodResolver;
use Sylius\MolliePlugin\Resolver\MolliePaymentsMethodResolverInterface;
use Sylius\MolliePlugin\Resolver\Order\PaymentCheckoutOrderResolver;
use Sylius\MolliePlugin\Resolver\Order\PaymentCheckoutOrderResolverInterface;
use Sylius\MolliePlugin\Resolver\PaymentLinkResolver;
use Sylius\MolliePlugin\Resolver\PaymentLinkResolverInterface;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolver;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Sylius\MolliePlugin\Resolver\PaymentMethodConfigResolver;
use Sylius\MolliePlugin\Resolver\PaymentMethodConfigResolverInterface;
use Sylius\MolliePlugin\Resolver\PaymentMethodResolver;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.resolver.payment_methods', MolliePaymentsMethodResolver::class)
        ->args([
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('sylius_mollie.resolver.mollie_countries_restriction'),
            service('sylius_mollie.voucher.checker.product_voucher_type'),
            service('sylius_mollie.resolver.order.payment_checkout_order'),
            service('sylius_mollie.repository.query.payment_method.mollie_based'),
            service('sylius_mollie.resolver.mollie_allowed_methods'),
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.resolver.mollie_factory_name'),
            service('sylius_mollie.provider.divisor'),
        ]);

    $services->alias(MolliePaymentsMethodResolverInterface::class, 'sylius_mollie.resolver.payment_methods');

    $services->set('sylius_mollie.resolver.payment_methods_image', MolliePaymentMethodImageResolver::class);

    $services->alias(MolliePaymentMethodImageResolverInterface::class, 'sylius_mollie.resolver.payment_methods_image');

    $services->set('sylius_mollie.resolver.payment_config', PaymentMethodConfigResolver::class)
        ->args([service('sylius_mollie.repository.mollie_gateway_config')]);

    $services->alias(PaymentMethodConfigResolverInterface::class, 'sylius_mollie.resolver.payment_config');

    $services->set('sylius_mollie.resolver.payment_locale', PaymentLocaleResolver::class);

    $services->alias(PaymentLocaleResolverInterface::class, 'sylius_mollie.resolver.payment_locale');

    $services->set('sylius_mollie.resolver.payment_link', PaymentLinkResolver::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius_mollie.converter.int_to_string'),
            service('sylius.repository.order'),
            service('sylius_mollie.mailer.manager.payment_link_email'),
            service('sylius_mollie.payum.provider.payment_token'),
        ]);

    $services->alias(PaymentLinkResolverInterface::class, 'sylius_mollie.resolver.payment_link');

    $services->set('sylius_mollie.resolver.mollie_countries_restriction', MollieCountriesRestrictionResolver::class)
        ->args([service('sylius_mollie.resolver.payment_methods_image')]);

    $services->alias(MollieCountriesRestrictionResolverInterface::class, 'sylius_mollie.resolver.mollie_countries_restriction');

    $services->set('sylius_mollie.resolver.mollie_factory_name', MollieFactoryNameResolver::class)
        ->args([service('sylius.context.cart')]);

    $services->alias(MollieFactoryNameResolverInterface::class, 'sylius_mollie.resolver.mollie_factory_name');

    $services->set('sylius_mollie.resolver.meal_voucher', MealVoucherResolver::class);

    $services->alias(MealVoucherResolverInterface::class, 'sylius_mollie.resolver.meal_voucher');

    $services->set('sylius_mollie.resolver.mollie_api_client_key', MollieApiClientKeyResolver::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.repository.query.payment_method.mollie_based'),
            service('sylius.context.channel'),
            service('sylius_mollie.resolver.mollie_factory_name'),
        ]);

    $services->alias(MollieApiClientKeyResolverInterface::class, 'sylius_mollie.resolver.mollie_api_client_key');

    $services->set('sylius_mollie.resolver.api_keys_test', ApiKeysTestResolver::class)
        ->args([service('sylius_mollie.creator.api_keys_test')]);

    $services->alias(ApiKeysTestResolverInterface::class, 'sylius_mollie.resolver.api_keys_test');

    $services->set('sylius_mollie.resolver.order.payment_checkout_order', PaymentCheckoutOrderResolver::class)
        ->args([
            service('request_stack'),
            service('sylius.context.cart'),
            service('sylius.repository.order'),
        ]);

    $services->alias(PaymentCheckoutOrderResolverInterface::class, 'sylius_mollie.resolver.order.payment_checkout_order');

    $services->set('sylius_mollie.resolver.mollie_methods', MollieMethodsResolver::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.client.mollie_api'),
            service('sylius.repository.gateway_config'),
            service('sylius_mollie.creator.mollie_methods'),
        ]);

    $services->alias(MollieMethodsResolverInterface::class, 'sylius_mollie.resolver.mollie_methods');

    $services->set('sylius_mollie.resolver.mollie_allowed_methods', MollieAllowedMethodsResolver::class)
        ->args([
            service('sylius_mollie.resolver.mollie_api_client_key'),
            service('sylius_mollie.resolver.payment_locale'),
            service('sylius_mollie.converter.int_to_string'),
        ]);

    $services->alias(MollieAllowedMethodsResolverInterface::class, 'sylius_mollie.resolver.mollie_allowed_methods');

    $services->set('sylius_mollie.payment_methods_resolver.mollie_payment', PaymentMethodResolver::class)
        ->decorate('sylius.resolver.payment_methods.default')
        ->args([
            service('.inner'),
            service('sylius_mollie.repository.query.payment_method.mollie_based'),
            service('sylius_mollie.resolver.mollie_factory_name'),
            service('sylius_mollie.filter.mollie_method'),
            service('doctrine.orm.entity_manager'),
        ])
        ->tag('sylius.payment_method_resolver', ['type' => 'mollie', 'label' => 'Mollie', 'priority' => 2]);
};
