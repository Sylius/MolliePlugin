<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Creator\AbandonedPaymentLinkCreator;
use Sylius\MolliePlugin\Creator\AbandonedPaymentLinkCreatorInterface;
use Sylius\MolliePlugin\Creator\ApiKeysTestCreator;
use Sylius\MolliePlugin\Creator\ApiKeysTestCreatorInterface;
use Sylius\MolliePlugin\Creator\MollieMethodsCreator;
use Sylius\MolliePlugin\Creator\MollieMethodsCreatorInterface;
use Sylius\MolliePlugin\Creator\PaymentDataCreator;
use Sylius\MolliePlugin\Creator\PaymentDataCreatorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.creator.abandoned_payment_link', AbandonedPaymentLinkCreator::class)
        ->args([
            service('sylius_mollie.resolver.payment_link'),
            service('sylius_mollie.repository.query.order.abandoned'),
            service('sylius_mollie.repository.query.payment_method.mollie_based'),
            service('sylius.repository.channel'),
            service('doctrine.orm.entity_manager'),
        ]);

    $services->alias(AbandonedPaymentLinkCreatorInterface::class, 'sylius_mollie.creator.abandoned_payment_link');

    $services->set('sylius_mollie.creator.mollie_methods', MollieMethodsCreator::class)
        ->args([
            service('sylius_mollie.factory.methods'),
            service('doctrine.orm.default_entity_manager'),
            service('sylius_mollie.custom_factory.mollie_gateway_config'),
        ]);

    $services->alias(MollieMethodsCreatorInterface::class, 'sylius_mollie.creator.mollie_methods');

    $services->set('sylius_mollie.creator.api_keys_test', ApiKeysTestCreator::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('translator'),
        ]);

    $services->alias(ApiKeysTestCreatorInterface::class, 'sylius_mollie.creator.api_keys_test');

    $services->set('sylius_mollie.creator.payment_data', PaymentDataCreator::class)
        ->args([
            service('sylius_mollie.converter.int_to_string'),
            service('router'),
            service('sylius_mollie.provider.payment_description'),
            service('sylius_mollie.resolver.payment_locale'),
            service('sylius_mollie.provider.divisor'),
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('sylius_mollie.converter.order'),
            '%locale%',
        ]);

    $services->alias(PaymentDataCreatorInterface::class, 'sylius_mollie.creator.payment_data');
};
