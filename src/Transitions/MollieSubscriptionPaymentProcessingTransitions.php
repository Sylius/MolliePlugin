<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Transitions;

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
