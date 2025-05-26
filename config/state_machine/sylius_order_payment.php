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
        'sylius_order_payment' => [
            'callbacks' => [
                'guard' => [
                    'sylius_mollie_order_payment_partially_refund_guard' => [
                        'on' => 'partially_refund',
                        'do' => ['@sylius_mollie.refund.guard.order_payment_refund', 'isRefundPossible'],
                        'args' => ['object'],
                    ],
                    'sylius_mollie_order_payment_refund_guard' => [
                        'on' => 'refund',
                        'do' => ['@sylius_mollie.refund.guard.order_payment_refund', 'isRefundPossible'],
                        'args' => ['object'],
                    ],
                ],
            ],
        ],
    ]);
};
