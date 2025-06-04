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
        'mollie_subscription_payment_state_graph' => [
            'class' => '%sylius_mollie.model.mollie_subscription.class%',
            'property_path' => 'paymentState',
            'graph' => 'mollie_subscription_payment_state_graph',
            'state_machine_class' => '%sylius.state_machine.class%',
            'states' => [
                'pending' => null,
                'ok' => null,
                'fail' => null,
            ],
            'transitions' => [
                'begin' => [
                    'from' => ['ok', 'fail'],
                    'to' => 'pending',
                ],
                'success' => [
                    'from' => ['pending'],
                    'to' => 'ok',
                ],
                'failure' => [
                    'from' => ['pending'],
                    'to' => 'fail',
                ],
            ],
            'callbacks' => [
                'before' => [
                    'mollie_subscription_payment_state_after_success' => [
                        'on' => ['success'],
                        'do' => ['@sylius_mollie.subscription.processor.subscription_schedule', 'process'],
                        'args' => ['object'],
                    ],
                ],
            ],
        ],
    ]);
};
