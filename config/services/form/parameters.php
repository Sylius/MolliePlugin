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

return static function (ContainerConfigurator $container) {
    $parameters = $container->parameters();
    $parameters->set('sylius_mollie.form.type.mollie_gateway_config.validation_groups', ['sylius']);
    $parameters->set('sylius_mollie.form.type.payment_methods.payment_surcharge_fee.validation_groups', ['sylius']);
    $parameters->set('sylius_mollie.form.type.mollie.validation_groups', ['sylius']);
};
