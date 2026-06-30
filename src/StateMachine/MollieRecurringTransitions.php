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

/**
 * @deprecated since Mollie 3.3 and will be removed in 4.0.
 *
 * @see https://github.com/Sylius/MolliePlugin/blob/3.3/UPGRADE-3.3.md for migration details
 */
final class MollieRecurringTransitions
{
    public const GRAPH_MANUAL = 'mollie_subscription_payment_graph_manual';

    public const TRANSITION_PAUSE = 'pause';

    public const TRANSITION_RESUME = 'resume';

    public const TRANSITION_CANCEL = 'cancel';

    private function __construct()
    {
    }
}
