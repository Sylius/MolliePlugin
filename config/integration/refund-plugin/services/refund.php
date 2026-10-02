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

use Sylius\MolliePlugin\Refund\Calculator\PaymentRefundCalculator;
use Sylius\MolliePlugin\Refund\Calculator\PaymentRefundCalculatorInterface;
use Sylius\MolliePlugin\Refund\Generator\PaymentNewUnitRefundGenerator;
use Sylius\MolliePlugin\Refund\Generator\PaymentNewUnitRefundGeneratorInterface;
use Sylius\MolliePlugin\Refund\Generator\PaymentRefundedGenerator;
use Sylius\MolliePlugin\Refund\Generator\PaymentRefundedGeneratorInterface;
use Sylius\MolliePlugin\Refund\OrderRefund;
use Sylius\MolliePlugin\Refund\OrderRefundInterface;
use Sylius\MolliePlugin\Refund\PaymentRefund;
use Sylius\MolliePlugin\Refund\PaymentRefundInterface;
use Sylius\MolliePlugin\Refund\Units\PaymentUnitsItemRefund;
use Sylius\MolliePlugin\Refund\Units\PaymentUnitsItemRefundInterface;
use Sylius\MolliePlugin\Refund\Units\ShipmentUnitRefund;
use Sylius\MolliePlugin\Refund\Units\ShipmentUnitRefundInterface;
use Sylius\MolliePlugin\Refund\Units\UnitsItemOrderRefund;
use Sylius\MolliePlugin\Refund\Units\UnitsItemOrderRefundInterface;
use Sylius\MolliePlugin\Refund\Units\UnitsShipmentOrderRefund;
use Sylius\MolliePlugin\Refund\Units\UnitsShipmentOrderRefundInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.refund.payment', PaymentRefund::class)
        ->args([
            service('sylius_mollie.command_bus'),
            service('sylius_mollie.refund.creator.payment_refund_command'),
            service('sylius_mollie.logger.mollie_logger_action'),
        ]);

    $services->alias(PaymentRefundInterface::class, 'sylius_mollie.refund.payment');

    $services->set('sylius_mollie.refund.units.shipment_unit', ShipmentUnitRefund::class);

    $services->alias(ShipmentUnitRefundInterface::class, 'sylius_mollie.refund.units.shipment_unit');

    $services->set('sylius_mollie.refund.units.payment_units_item', PaymentUnitsItemRefund::class)
        ->args([
            service('sylius_mollie.refund.generator.payment'),
            service('sylius_mollie.refund_generator.payment_new_unit'),
            service('sylius_mollie.refund.calculator.payment'),
        ]);

    $services->alias(PaymentUnitsItemRefundInterface::class, 'sylius_mollie.refund.units.payment_units_item');

    $services->set('sylius_mollie.refund.order', OrderRefund::class)
        ->args([
            service('sylius.command_bus'),
            service('sylius_mollie.refund.creator.order_refund_command'),
            service('sylius_mollie.logger.mollie_logger_action'),
        ]);

    $services->alias(OrderRefundInterface::class, 'sylius_mollie.refund.order');

    $services->set('sylius_mollie.refund.units.units_item_order', UnitsItemOrderRefund::class)
        ->args([service('sylius_refund.repository.refund')]);

    $services->alias(UnitsItemOrderRefundInterface::class, 'sylius_mollie.refund.units.units_item_order');

    $services->set('sylius_mollie.refund.units.units_shipment_order', UnitsShipmentOrderRefund::class)
        ->args([service('sylius_refund.repository.refund')]);

    $services->alias(UnitsShipmentOrderRefundInterface::class, 'sylius_mollie.refund.units.units_shipment_order');

    $services->set('sylius_mollie.refund_generator.payment_new_unit', PaymentNewUnitRefundGenerator::class);

    $services->alias(PaymentNewUnitRefundGeneratorInterface::class, 'sylius_mollie.refund_generator.payment_new_unit');

    $services->set('sylius_mollie.refund.generator.payment', PaymentRefundedGenerator::class)
        ->args([service('sylius_refund.repository.refund')]);

    $services->alias(PaymentRefundedGeneratorInterface::class, 'sylius_mollie.refund.generator.payment');

    $services->set('sylius_mollie.refund.calculator.payment', PaymentRefundCalculator::class);

    $services->alias(PaymentRefundCalculatorInterface::class, 'sylius_mollie.refund.calculator.payment');
};
