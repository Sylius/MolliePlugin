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

final class MollieSubscriptionPaymentProcessingTransitions
{
    public const GRAPH = 'mollie_subscription_payment_state_graph';

    public const TRANSITION_BEGIN = 'begin';

    public const TRANSITION_SUCCESS = 'success';

    public const TRANSITION_FAILURE = 'failure';

    private function __construct()
    {
    }
}
