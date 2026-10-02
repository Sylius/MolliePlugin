<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\EventListener\Workflow\Payment\PaymentRefundGuardListener;
use Sylius\MolliePlugin\EventListener\Workflow\Payment\SubscriptionPaymentFailListener;
use Sylius\MolliePlugin\EventListener\Workflow\Payment\SubscriptionPaymentSuccessListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.listener.workflow.payment.subscription_payment_fail', SubscriptionPaymentFailListener::class)
        ->args([service('sylius_mollie.subscription.processor.subscription_payment')])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_payment.completed.fail', 'priority' => -100])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_payment.completed.cancel', 'priority' => -100]);

    $services->set('sylius_mollie.listener.workflow.payment.subscription_payment_success', SubscriptionPaymentSuccessListener::class)
        ->args([service('sylius_mollie.subscription.processor.subscription_payment')])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_payment.completed.complete', 'priority' => -100]);

    $services->set('sylius_mollie.listener.workflow.payment.refund_guard', PaymentRefundGuardListener::class)
        ->args([service('sylius_mollie.refund.guard.mollie_payment_refund')])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_payment.guard.refund', 'priority' => 0]);
};
