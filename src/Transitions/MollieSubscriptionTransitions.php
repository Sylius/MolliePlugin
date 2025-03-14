<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Transitions;

final class MollieSubscriptionTransitions
{
    public const GRAPH = 'mollie_subscription_graph';

    public const TRANSITION_PROCESS = 'process';

    public const TRANSITION_ACTIVATE = 'activate';

    public const TRANSITION_CANCEL = 'cancel';

    public const TRANSITION_SUSPEND = 'suspend';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_ABORT = 'abort';

    private function __construct()
    {
    }
}
