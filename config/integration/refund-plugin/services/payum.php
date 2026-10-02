<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Payum\Action\Refund\RefundAction;
use Sylius\MolliePlugin\Payum\Action\Refund\RefundOrderAction;
use Sylius\MolliePlugin\Payum\Action\StatusAction;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.payum.action.refund.refund', RefundAction::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.refund.converter.refund_data'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.refund'])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.refund_subscription']);

    $services->set('sylius_mollie.payum.action.refund.refund.refund_order', RefundOrderAction::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.refund.converter.refund_data'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.refund_order']);

    $services->set('sylius_mollie.payum.action.status', StatusAction::class)
        ->args([
            service('sylius_mollie.refund.payment'),
            service('sylius_mollie.refund.order'),
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.voucher.updater.order_voucher_adjustment'),
            service('sylius_mollie.refund.checker.mollie_order_refund'),
        ])
        ->tag('payum.action', ['factory' => 'mollie', 'alias' => 'payum.action.status'])
        ->tag('payum.action', ['factory' => 'mollie_subscription', 'alias' => 'payum.action.status_subscription']);
};
