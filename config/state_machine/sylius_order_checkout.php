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
use winzou\Bundle\StateMachineBundle\winzouStateMachineBundle;

return static function (ContainerConfigurator $container) {
    if (!class_exists(winzouStateMachineBundle::class)) {
        return;
    }

    $container->extension('winzou_state_machine', [
        'sylius_order_checkout' => [
            'callbacks' => [
                'after' => [
                    'sylius_mollie_refresh_mollie_payment_methods' => [
                        'on' => ['address'],
                        'do' => ["@sylius_mollie.synchronizer.mollie_payment_methods", "sync"],
                    ],
                ],
            ],
        ],
    ]);
};
