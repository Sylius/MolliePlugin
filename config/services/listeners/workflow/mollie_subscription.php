<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\EventListener\Workflow\MollieSubscription\AbortSubscriptionGuardListener;
use Sylius\MolliePlugin\EventListener\Workflow\MollieSubscription\ActivateSubscriptionProcessListener;
use Sylius\MolliePlugin\EventListener\Workflow\MollieSubscription\CompleteSubscriptionGuardListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.listener.workflow.mollie_subscription.complete_subscription_guard', CompleteSubscriptionGuardListener::class)
        ->args([service('sylius_mollie.subscription.guard.subscription')])
        ->tag('kernel.event_listener', ['event' => 'workflow.mollie_subscription_graph.guard.complete', 'priority' => 0]);

    $services->set('sylius_mollie.listener.workflow.mollie_subscription.abort_subscription_guard', AbortSubscriptionGuardListener::class)
        ->args([service('sylius_mollie.subscription.guard.subscription')])
        ->tag('kernel.event_listener', ['event' => 'workflow.mollie_subscription_graph.guard.abort', 'priority' => 0]);

    $services->set('sylius_mollie.listener.workflow.mollie_subscription.activate_subscription_process', ActivateSubscriptionProcessListener::class)
        ->args([service('sylius_mollie.subscription.processor.subscription_schedule')])
        ->tag('kernel.event_listener', ['event' => 'workflow.mollie_subscription_graph.completed.activate', 'priority' => 0]);
};
