<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if (!class_exists(\Sylius\RefundPlugin\SyliusRefundPlugin::class)) {
        return;
    }

    $container->import('@SyliusRefundPlugin/config/config.yaml');

    $container->extension('sylius_refund', [
        'pdf_generator' => [
            'enabled' => false,
        ],
    ]);

    $container->parameters()->set('sylius_refund.supported_gateways', ['offline', 'mollie']);
};
