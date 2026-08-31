<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Factory\DatePeriodFactory;
use Sylius\MolliePlugin\Factory\DatePeriodFactoryInterface;
use Sylius\MolliePlugin\Factory\MethodsFactory;
use Sylius\MolliePlugin\Factory\MethodsFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayConfigFactory;
use Sylius\MolliePlugin\Factory\MollieGatewayConfigFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieLoggerFactory;
use Sylius\MolliePlugin\Factory\MollieLoggerFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionFactory;
use Sylius\MolliePlugin\Factory\MollieSubscriptionFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionScheduleFactory;
use Sylius\MolliePlugin\Factory\MollieSubscriptionScheduleFactoryInterface;
use Sylius\MolliePlugin\Factory\PaymentDetailsFactory;
use Sylius\MolliePlugin\Factory\PaymentDetailsFactoryInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.custom_factory.mollie_gateway_config', MollieGatewayConfigFactory::class)
        ->decorate('sylius_mollie.factory.mollie_gateway_config')
        ->args([
            service('.inner'),
            service('sylius_mollie.repository.mollie_gateway_config'),
        ]);

    $services->alias(MollieGatewayConfigFactoryInterface::class, 'sylius_mollie.custom_factory.mollie_gateway_config');

    $services->set('sylius_mollie.custom_factory.mollie_logger', MollieLoggerFactory::class)
        ->decorate('sylius_mollie.factory.mollie_logger')
        ->args([service('.inner')]);

    $services->alias(MollieLoggerFactoryInterface::class, 'sylius_mollie.custom_factory.mollie_logger');

    $services->set('sylius_mollie.custom_factory.mollie_subscription', MollieSubscriptionFactory::class)
        ->decorate('sylius_mollie.factory.mollie_subscription')
        ->args([
            service('.inner'),
            service('router'),
        ]);

    $services->alias(MollieSubscriptionFactoryInterface::class, 'sylius_mollie.custom_factory.mollie_subscription');

    $services->set('sylius_mollie.custom_factory.mollie_subscription_schedule', MollieSubscriptionScheduleFactory::class)
        ->decorate('sylius_mollie.factory.mollie_subscription_schedule')
        ->args([service('.inner')]);

    $services->alias(MollieSubscriptionScheduleFactoryInterface::class, 'sylius_mollie.custom_factory.mollie_subscription_schedule');

    $services->set('sylius_mollie.factory.methods', MethodsFactory::class);

    $services->alias(MethodsFactoryInterface::class, 'sylius_mollie.factory.methods');

    $services->set('sylius_mollie.factory.date_period', DatePeriodFactory::class);

    $services->alias(DatePeriodFactoryInterface::class, 'sylius_mollie.factory.date_period');

    $services->set('sylius_mollie.factory.payment_details', PaymentDetailsFactory::class);

    $services->alias(PaymentDetailsFactoryInterface::class, 'sylius_mollie.factory.payment_details');
};
