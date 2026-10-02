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

use Sylius\MolliePlugin\Voucher\Checker\ProductVoucherTypeChecker;
use Sylius\MolliePlugin\Voucher\Checker\ProductVoucherTypeCheckerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.voucher.checker.product_voucher_type', ProductVoucherTypeChecker::class)
        ->public()
        ->args([service('sylius_mollie.repository.mollie_gateway_config')]);

    $services->alias(ProductVoucherTypeCheckerInterface::class, 'sylius_mollie.voucher.checker.product_voucher_type');
};
