<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\StateMachine\Transition;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use SM\Factory\FactoryInterface;

final class PaymentStateMachineTransition implements PaymentStateMachineTransitionInterface
{
    /** @var FactoryInterface */
    private $subscriptionStateMachineFactory;

    public function __construct(FactoryInterface $subscriptionStateMachineFactory)
    {
        $this->subscriptionStateMachineFactory = $subscriptionStateMachineFactory;
    }

    public function apply(
        MollieSubscriptionInterface $subscription,
        string $transitions
    ): void {
        $stateMachine = $this->subscriptionStateMachineFactory->get(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::GRAPH
        );

        if (!$stateMachine->can($transitions)) {
            return;
        }

        $stateMachine->apply($transitions);
    }
}
