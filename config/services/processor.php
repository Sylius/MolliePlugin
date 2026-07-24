<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Processor\PaymentSurchargeCleanupProcessor;
use Sylius\MolliePlugin\Processor\PaymentSurchargeProcessor;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.processor.payment_surcharge', PaymentSurchargeProcessor::class)
        ->args([service('sylius_mollie.calculator.payment_fee.composite')])
        ->tag('sylius.order_processor', ['priority' => 10]);

    $services->set('sylius_mollie.processor.payment_surcharge_cleanup', PaymentSurchargeCleanupProcessor::class)
        ->args([service('sylius_mollie.calculator.clearer.payment_fee_adjustment')])
        ->tag('sylius.order_processor', ['priority' => 20]);
};
