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

use Sylius\MolliePlugin\Refund\Creator\OrderRefundCommandCreator;
use Sylius\MolliePlugin\Refund\Creator\OrderRefundCommandCreatorInterface;
use Sylius\MolliePlugin\Refund\Creator\PaymentRefundCommandCreator;
use Sylius\MolliePlugin\Refund\Creator\PaymentRefundCommandCreatorInterface;
use Sylius\RefundPlugin\Provider\RefundPaymentMethodsProviderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.refund.creator.payment_refund_command', PaymentRefundCommandCreator::class)
        ->args([
            service('sylius.repository.order'),
            service('sylius_refund.repository.refund'),
            service('sylius_mollie.refund.units.payment_units_item'),
            service('sylius_mollie.refund.units.shipment_unit'),
            service(RefundPaymentMethodsProviderInterface::class),
            service('sylius_mollie.provider.divisor'),
        ]);

    $services->alias(PaymentRefundCommandCreatorInterface::class, 'sylius_mollie.refund.creator.payment_refund_command');

    $services->set('sylius_mollie.refund.creator.order_refund_command', OrderRefundCommandCreator::class)
        ->args([
            service('sylius.repository.order'),
            service('sylius_mollie.refund.units.units_item_order'),
            service('sylius_mollie.refund.units.units_shipment_order'),
            service(RefundPaymentMethodsProviderInterface::class),
        ]);

    $services->alias(OrderRefundCommandCreatorInterface::class, 'sylius_mollie.refund.creator.order_refund_command');
};
