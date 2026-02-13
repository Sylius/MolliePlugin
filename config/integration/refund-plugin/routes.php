<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    if (class_exists(\Sylius\RefundPlugin\SyliusRefundPlugin::class)) {
        $routes->import('routes/**/*.yaml');
    }
};
