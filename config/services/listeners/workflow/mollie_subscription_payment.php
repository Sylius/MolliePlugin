<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\EventListener\Workflow\MollieSubscriptionPayment\SubscriptionSuccessProcessListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.listener.workflow.mollie_subscription_payment.subscription_success_process', SubscriptionSuccessProcessListener::class)
        ->args([service('sylius_mollie.subscription.processor.subscription_schedule')])
        ->tag('kernel.event_listener', ['event' => 'workflow.mollie_subscription_payment_state_graph.transition.success', 'priority' => 0]);
};
