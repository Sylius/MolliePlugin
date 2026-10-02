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
