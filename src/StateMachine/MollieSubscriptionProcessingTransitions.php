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

namespace Sylius\MolliePlugin\StateMachine;

final class MollieSubscriptionProcessingTransitions
{
    public const GRAPH = 'mollie_subscription_processing_graph';

    public const TRANSITION_PROCESS = 'process';

    public const TRANSITION_SCHEDULE = 'schedule';

    private function __construct()
    {
    }
}
