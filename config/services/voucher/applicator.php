<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Voucher\Applicator\OrderVouchersApplicator;
use Sylius\MolliePlugin\Voucher\Applicator\OrderVouchersApplicatorInterface;
use Sylius\MolliePlugin\Voucher\Applicator\UnitsVouchersApplicator;
use Sylius\MolliePlugin\Voucher\Applicator\UnitsVouchersApplicatorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.voucher.applicator.units_promotion_adjustments', UnitsVouchersApplicator::class)
        ->args([
            service('sylius.custom_factory.adjustment'),
            service('sylius.distributor.integer'),
        ]);

    $services->alias(UnitsVouchersApplicatorInterface::class, 'sylius_mollie.voucher.applicator.units_promotion_adjustments');

    $services->set('sylius_mollie.voucher.applicator.order_vouchers', OrderVouchersApplicator::class)
        ->args([
            service('sylius.distributor.proportional_integer'),
            service('sylius_mollie.voucher.applicator.units_promotion_adjustments'),
        ]);

    $services->alias(OrderVouchersApplicatorInterface::class, 'sylius_mollie.voucher.applicator.order_vouchers');
};
