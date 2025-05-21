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
        'sylius_payment' => [
            'callbacks' => [
                'after' => [
                    'sylius_process_subscription_fail' => [
                        'on' => ['fail'],
                        'do' => ['@sylius_mollie.subscription.processor.subscription_payment', 'processFailed'],
                        'args' => ['object'],
                    ],
                    'sylius_process_subscription_success' => [
                        'on' => ['complete'],
                        'do' => ['@sylius_mollie.subscription.processor.subscription_payment', 'processSuccess'],
                        'args' => ['object'],
                    ],
                ],
            ],
        ],
    ]);
};
