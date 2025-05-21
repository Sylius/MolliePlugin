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
        'mollie_subscription_processing' => [
            'class' => '%sylius_mollie.model.mollie_subscription.class%',
            'property_path' => 'processingState',
            'graph' => 'mollie_subscription_processing_graph',
            'state_machine_class' => '%sylius.state_machine.class%',
            'states' => [
                'none' => null,
                'pending' => null,
                'processing' => null,
            ],
            'transitions' => [
                'process' => [
                    'from' => ['none', 'pending'],
                    'to' => 'processing',
                ],
                'schedule' => [
                    'from' => ['processing', 'none'],
                    'to' => 'pending',
                ],
            ],
        ],
    ]);
};
