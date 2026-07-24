<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Payum\Core\Request\GetHttpRequest;
use Sylius\MolliePlugin\Payum\Action\CaptureAction;
use Sylius\MolliePlugin\Payum\Action\ConvertMolliePaymentAction;
use Sylius\MolliePlugin\Payum\Action\CreateCustomerAction;
use Sylius\MolliePlugin\Payum\Action\CreateOnDemandPaymentAction;
use Sylius\MolliePlugin\Payum\Action\CreateOrderAction;
use Sylius\MolliePlugin\Payum\Action\CreatePaymentAction;
use Sylius\MolliePlugin\Payum\Action\NotifyAction;
use Sylius\MolliePlugin\Payum\Action\StatusAction;
use Sylius\MolliePlugin\Payum\Action\Subscription\CancelRecurringSubscriptionAction;
use Sylius\MolliePlugin\Payum\Action\Subscription\ConvertMollieSubscriptionPaymentAction;
use Sylius\MolliePlugin\Payum\Action\Subscription\CreateInternalSubscriptionAction;
use Sylius\MolliePlugin\Payum\Action\Subscription\CreateOnDemandSubscriptionAction;
use Sylius\MolliePlugin\Payum\Action\Subscription\StatusRecurringSubscriptionAction;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.payum.get_http_request', GetHttpRequest::class);

    $services->set('sylius_mollie.payum.action.capture', CaptureAction::class)
        ->args([
            service('sylius.repository.order'),
            service('sylius_mollie.resolver.mollie_api_client_key'),
            service('sylius.repository.payment'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.capture'])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.capture_subscription']);

    $services->set('sylius_mollie.payum.action.notify', NotifyAction::class)
        ->args([
            service('sylius_mollie.payum.get_http_request'),
            service('sylius_mollie.repository.mollie_subscription'),
            service('sylius_mollie.state_machine.order_set_status'),
            service('sylius_mollie.logger.mollie_logger_action'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.notify'])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.notify_subscription']);

    $services->set('sylius_mollie.payum.action.status', StatusAction::class)
        ->args([
            service('sylius_mollie.refund.payment')->nullOnInvalid(),
            service('sylius_mollie.refund.order')->nullOnInvalid(),
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.voucher.updater.order_voucher_adjustment'),
            service('sylius_mollie.refund.checker.mollie_order_refund')->nullOnInvalid(),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.status'])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.status_subscription']);

    $services->set('sylius_mollie.payum.action.convert_mollie_payment', ConvertMolliePaymentAction::class)
        ->args([
            service('sylius_mollie.provider.payment_description'),
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('sylius_mollie.converter.order'),
            service('sylius.context.customer'),
            service('sylius_mollie.resolver.payment_locale'),
            service('sylius_mollie.payum.factory.create_customer'),
            service('sylius_mollie.converter.int_to_string'),
            service('sylius_mollie.provider.divisor'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.convert_mollie_payment']);

    $services->set('sylius_mollie.payum.action.subscription.convert_mollie_subscription_payment', ConvertMollieSubscriptionPaymentAction::class)
        ->args([
            service('sylius_mollie.provider.payment_description'),
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('sylius_mollie.converter.order'),
            service('sylius.context.customer'),
            service('sylius_mollie.resolver.payment_locale'),
            service('sylius_mollie.converter.int_to_string'),
            service('sylius_mollie.provider.divisor'),
        ])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.convert_mollie_subscription_payment']);

    $services->set('sylius_mollie.payum.action.create_payment', CreatePaymentAction::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.client.parser.api_exception'),
            service('request_stack'),
            service('sylius_mollie.repository.mollie_customer'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.create_payment'])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.create_payment_subscription']);

    $services->set('sylius_mollie.payum.action.create_order', CreateOrderAction::class)
        ->args([
            service('sylius_mollie.resolver.payment_config'),
            service('sylius_mollie.logger.mollie_logger_action'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.create_order']);

    $services->set('sylius_mollie.payum.action.create_on_demand_payment', CreateOnDemandPaymentAction::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.client.parser.api_exception'),
        ])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.create_on_demand_payment_action']);

    $services->set('sylius_mollie.payum.action.subscription.create_on_demand_subscription', CreateOnDemandSubscriptionAction::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.client.parser.api_exception'),
        ])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.create_on_demand_subscription_action']);

    $services->set('sylius_mollie.payum.action.subscription.create_internal_subscription', CreateInternalSubscriptionAction::class)
        ->args([
            service('sylius_mollie.repository.mollie_subscription'),
            service('sylius_mollie.custom_factory.mollie_subscription'),
            service('sylius.repository.order'),
        ])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.create_internal_subscription']);

    $services->set('sylius_mollie.payum.action.create_customer', CreateCustomerAction::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.repository.mollie_customer'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.api.create_customer'])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.api.create_customer_subscription']);

    $services->set('sylius_mollie.payum.action.subscription.cancel_recurring_subscription', CancelRecurringSubscriptionAction::class)
        ->args([service('sylius_mollie.logger.mollie_logger_action')])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.state_machine.cancel_recurring_subscription']);

    $services->set('sylius_mollie.payum.action.subscription.status_recurring_subscription', StatusRecurringSubscriptionAction::class)
        ->args([
            service('sylius_mollie.manager.mollie_subscription'),
            service('sylius_mollie.state_machine.applicator.subscription_and_payment_id'),
            service('sylius_mollie.state_machine.applicator.subscription_and_sylius_payment'),
            service('sylius_abstraction.state_machine'),
        ])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.state_machine.status_recurring_subscription']);
};
