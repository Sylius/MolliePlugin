<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\EventListener\Workflow\OrderPayment\RefundGuardListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.listener.workflow.order_payment.refund_guard', RefundGuardListener::class)
        ->args([service('sylius_mollie.refund.guard.order_payment_refund')])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_order_payment.guard.partially_refund', 'priority' => 0])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_order_payment.guard.refund', 'priority' => 0]);
};
