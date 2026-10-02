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

use Sylius\MolliePlugin\Purifier\MolliePaymentMethodPurifier;
use Sylius\MolliePlugin\Purifier\MolliePaymentMethodPurifierInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.purifier.mollie_payment_method', MolliePaymentMethodPurifier::class)
        ->args([service('sylius_mollie.repository.mollie_gateway_config')]);

    $services->alias(MolliePaymentMethodPurifierInterface::class, 'sylius_mollie.purifier.mollie_payment_method');
};
