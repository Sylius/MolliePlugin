<?php

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
