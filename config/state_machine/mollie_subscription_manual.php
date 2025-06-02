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
        'mollie_subscription_manual' => [
            'class' => '%sylius_mollie.model.mollie_subscription.class%',
            'property_path' => 'state',
            'graph' => 'mollie_subscription_payment_graph_manual',
            'state_machine_class' => '%sylius.state_machine.class%',
            'states' => [
                'new' => null,
                'active' => null,
                'processing' => null,
                'paused' => null,
                'canceled' => null,
                'suspended' => null,
                'completed' => null,
                'aborted' => null,
            ],
            'transitions' => [
                'pause' => [
                    'from' => ['active'],
                    'to' => 'paused',
                ],
                'resume' => [
                    'from' => ['paused'],
                    'to' => 'active',
                ],
                'cancel' => [
                    'from' => ['new', 'active', 'processing'],
                    'to' => 'canceled',
                ],
            ],
        ],
    ]);
};
