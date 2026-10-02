<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\EventListener\PaymentPartialEventListener;
use Sylius\MolliePlugin\EventListener\RefundPaymentGeneratedAutoCompleteListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.listener.payment_partial', PaymentPartialEventListener::class)
        ->args([
            service('sylius_mollie.refund.handler.order_payment_refund'),
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius.repository.order'),
            service('sylius_mollie.resolver.mollie_api_client_key'),
            service('sylius_mollie.provider.divisor'),
            service('sylius_refund.repository.refund'),
        ])
        ->tag('sylius_refund.units_refunded.process_step', ['priority' => 0]);

    $services->set('sylius_mollie.listener.refund_payment_generated_auto_complete', RefundPaymentGeneratedAutoCompleteListener::class)
        ->args([
            service('sylius_refund.repository.refund_payment'),
            service('sylius_refund.state_resolver.refund_payment_completed_applier'),
            service('sylius.repository.payment_method'),
        ])
        ->tag('messenger.message_handler', ['priority' => 10]);
};
