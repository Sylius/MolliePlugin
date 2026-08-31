<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Refund\Guard\OrderPaymentRefundGuard;
use Sylius\MolliePlugin\Refund\Guard\OrderPaymentRefundGuardInterface;
use Sylius\MolliePlugin\Refund\Guard\PaymentRefundGuard;
use Sylius\MolliePlugin\Refund\Guard\PaymentRefundGuardInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $container->import('services/**/*.php');
    $container->import('integration/refund-plugin/services.php');


    $services->set('sylius_mollie.refund.guard.mollie_payment_refund', PaymentRefundGuard::class)
        ->public()
        ->args(['%kernel.bundles%']);

    $services->alias(PaymentRefundGuardInterface::class, 'sylius_mollie.refund.guard.mollie_payment_refund');

    $services->set('sylius_mollie.refund.guard.order_payment_refund', OrderPaymentRefundGuard::class)
        ->public();

    $services->alias(OrderPaymentRefundGuardInterface::class, 'sylius_mollie.refund.guard.order_payment_refund');
};
