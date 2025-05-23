<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if (class_exists(\Sylius\RefundPlugin\SyliusRefundPlugin::class)) {
        $container->import('config/**/*.yaml');
    }
};
