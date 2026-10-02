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

use Sylius\MolliePlugin\Refund\Validator\RefundUnitsCommandValidator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.refund.validator.refund_units_command', RefundUnitsCommandValidator::class)
        ->decorate('sylius_refund.validator.refund_units_command')
        ->args([
            service('sylius_refund.checker.order_refunding_availability'),
            service('sylius_refund.validator.refund_amount'),
            service('sylius_mollie.refund.checker.duplicate_refund_the_same_amount'),
        ]);
};
