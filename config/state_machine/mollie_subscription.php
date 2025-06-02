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
        'mollie_subscription' => [
            'class' => '%sylius_mollie.model.mollie_subscription.class%',
            'property_path' => 'state',
            'graph' => 'mollie_subscription_graph',
            'state_machine_class' => '%sylius.state_machine.class%',
            'states' => [
                'new' => null,
                'processing' => null,
                'active' => null,
                'paused' => null,
                'canceled' => null,
                'suspended' => null,
                'completed' => null,
                'aborted' => null,
            ],
            'transitions' => [
                'process' => [
                    'from' => ['new', 'active', 'suspended', 'completed'],
                    'to' => 'processing',
                ],
                'activate' => [
                    'from' => ['new', 'processing', 'suspended', 'completed'],
                    'to' => 'active',
                ],
                'cancel' => [
                    'from' => ['processing', 'active', 'suspended', 'completed'],
                    'to' => 'canceled',
                ],
                'suspend' => [
                    'from' => ['new', 'processing', 'active', 'completed'],
                    'to' => 'suspended',
                ],
                'complete' => [
                    'from' => ['new', 'processing', 'active', 'suspended'],
                    'to' => 'completed',
                ],
                'abort' => [
                    'from' => ['new', 'processing', 'active', 'suspended', 'completed'],
                    'to' => 'aborted',
                ],
            ],
            'callbacks' => [
                'guard' => [
                    'sylius_mollie_plugin_activate_recurring_subscription_guard_for_complete' => [
                        'on' => 'complete',
                        'do' => ['@sylius_mollie.subscription.guard.subscription', 'isCompletable'],
                        'args' => ['object'],
                    ],
                    'sylius_mollie_plugin_activate_recurring_subscription_guard_for_abort' => [
                        'on' => 'abort',
                        'do' => ['@sylius_mollie.subscription.guard.subscription', 'isEligibleForPaymentsAbort'],
                        'args' => ['object'],
                    ],
                ],
                'after' => [
                    'sylius_mollie_plugin_activate_recurring_subscription_process' => [
                        'on' => ['activate'],
                        'do' => ['@sylius_mollie.subscription.processor.subscription_schedule', 'processScheduleGeneration'],
                        'args' => ['object'],
                    ],
                ],
            ],
        ],
    ]);
};
