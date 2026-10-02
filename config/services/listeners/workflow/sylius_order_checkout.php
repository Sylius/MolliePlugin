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

use Sylius\MolliePlugin\EventListener\Workflow\OrderCheckout\RefreshPaymentMethodsListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.listener.workflow.order_checkout.address.refresh_payment_methods', RefreshPaymentMethodsListener::class)
        ->args([service('sylius_mollie.synchronizer.mollie_payment_methods')])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_order_checkout.completed.address', 'priority' => 0]);
};
