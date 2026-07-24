<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicator;
use Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.state_machine.applicator.subscription_and_payment_id', SubscriptionAndPaymentIdApplicator::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius_abstraction.state_machine'),
        ]);

    $services->alias(SubscriptionAndPaymentIdApplicatorInterface::class, 'sylius_mollie.state_machine.applicator.subscription_and_payment_id');

    $services->set('sylius_mollie.state_machine.applicator.subscription_and_sylius_payment', SubscriptionAndSyliusPaymentApplicator::class)
        ->args([service('sylius_abstraction.state_machine')]);

    $services->alias(SubscriptionAndSyliusPaymentApplicatorInterface::class, 'sylius_mollie.state_machine.applicator.subscription_and_sylius_payment');

    $services->set('sylius_mollie.state_machine.order_set_status', MollieOrderStatesApplicator::class)
        ->args([
            service('sylius_abstraction.state_machine'),
            service('sylius.repository.order'),
        ]);

    $services->alias(MollieOrderStatesApplicatorInterface::class, 'sylius_mollie.state_machine.order_set_status');
};
