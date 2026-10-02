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

use Sylius\MolliePlugin\Shipping\MollieShipmentNotifier;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.shipping.mollie_shipment_notifier', MollieShipmentNotifier::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius.section_resolver.uri_based'),
        ]);
};
