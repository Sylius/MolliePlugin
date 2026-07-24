<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Subscription\Processor\CancelRecurringSubscriptionProcessor;
use Sylius\MolliePlugin\Subscription\Processor\CancelRecurringSubscriptionProcessorInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionPaymentProcessor;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionPaymentProcessorInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionProcessor;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionProcessorInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionScheduleProcessor;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionScheduleProcessorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.subscription.processor.subscription', SubscriptionProcessor::class)
        ->args([
            service('sylius_mollie.cloner.subscription_order'),
            service('sylius.custom_factory.payment'),
            service('sylius.repository.order'),
            service('sylius_mollie.factory.payment_details'),
            service('sylius_mollie.repository.mollie_subscription'),
            service('payum'),
            service('sylius.repository.gateway_config'),
        ]);

    $services->alias(SubscriptionProcessorInterface::class, 'sylius_mollie.subscription.processor.subscription');

    $services->set('sylius_mollie.subscription.processor.subscription_schedule', SubscriptionScheduleProcessor::class)
        ->args([
            service('sylius_mollie.repository.mollie_subscription_schedule'),
            service('sylius_mollie.subscription.generator.subscription_schedule'),
        ]);

    $services->alias(SubscriptionScheduleProcessorInterface::class, 'sylius_mollie.subscription.processor.subscription_schedule');

    $services->set('sylius_mollie.subscription.processor.cancel_recurring_subscription', CancelRecurringSubscriptionProcessor::class)
        ->args([service('payum')]);

    $services->alias(CancelRecurringSubscriptionProcessorInterface::class, 'sylius_mollie.subscription.processor.cancel_recurring_subscription');

    $services->set('sylius_mollie.subscription.processor.subscription_payment', SubscriptionPaymentProcessor::class)
        ->args([
            service('sylius_mollie.repository.mollie_subscription'),
            service('payum'),
        ]);

    $services->alias(SubscriptionPaymentProcessorInterface::class, 'sylius_mollie.subscription.processor.subscription_payment');
};
