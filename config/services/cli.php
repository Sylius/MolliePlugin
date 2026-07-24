<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Console\Command\BeginProcessingSubscriptions;
use Sylius\MolliePlugin\Console\Command\ProcessSubscriptions;
use Sylius\MolliePlugin\Console\Command\SendAbandonedPaymentLink;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.console.command.send_abandoned_payment_link', SendAbandonedPaymentLink::class)
        ->args([service('sylius_mollie.creator.abandoned_payment_link')])
        ->tag('console.command');

    $services->set('sylius_mollie.console.command.subscription.begin_processing', BeginProcessingSubscriptions::class)
        ->args([
            service('sylius_mollie.repository.mollie_subscription'),
            service('sylius_abstraction.state_machine'),
        ])
        ->tag('console.command');

    $services->set('sylius_mollie.console.command.subscription.process', ProcessSubscriptions::class)
        ->args([
            service('sylius_mollie.repository.mollie_subscription'),
            service('sylius_abstraction.state_machine'),
            service('sylius_mollie.subscription.processor.subscription'),
            service('router'),
        ])
        ->tag('console.command');
};
