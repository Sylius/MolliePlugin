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

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    if (!class_exists(\Sylius\RefundPlugin\SyliusRefundPlugin::class)) {
        return;
    }

    $routes->import('@SyliusRefundPlugin/config/routes.yaml');
};
