<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefund;
use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefundInterface;
use Sylius\RefundPlugin\Filter\UnitRefundFilterInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.refund.handler.order_payment_refund', OrderPaymentRefund::class)
        ->args([
            service('sylius.repository.order'),
            service('sylius_mollie.logger.mollie_logger_action'),
            service('payum'),
            service(UnitRefundFilterInterface::class),
        ]);

    $services->alias(OrderPaymentRefundInterface::class, 'sylius_mollie.refund.handler.order_payment_refund');
};
