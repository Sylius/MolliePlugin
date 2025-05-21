<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use winzou\Bundle\StateMachineBundle\winzouStateMachineBundle;

return static function (ContainerConfigurator $container) {
    if (!class_exists(winzouStateMachineBundle::class)) {
        return;
    }

    $container->extension('winzou_state_machine', [
        'sylius_order_checkout' => [
            'callbacks' => [
                'after' => [
                    'sylius_mollie_plugin_payment_surcharge' => [
                        'on' => ['select_payment'],
                        'do' => ['@sylius_mollie.processor.payment_surcharge', 'process'],
                        'args' => ['object'],
                    ],
                ],
            ],
        ],
    ]);
};
