<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
