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

use Sylius\MolliePlugin\Voucher\Updater\OrderVoucherAdjustmentUpdater;
use Sylius\MolliePlugin\Voucher\Updater\OrderVoucherAdjustmentUpdaterInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.voucher.updater.order_voucher_adjustment', OrderVoucherAdjustmentUpdater::class)
        ->public()
        ->args([
            service('sylius.repository.order'),
            service('sylius_mollie.voucher.applicator.order_vouchers'),
            service('sylius_mollie.provider.divisor'),
        ]);

    $services->alias(OrderVoucherAdjustmentUpdaterInterface::class, 'sylius_mollie.voucher.updater.order_voucher_adjustment');
};
