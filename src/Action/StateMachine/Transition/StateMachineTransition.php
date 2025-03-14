<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\StateMachine\Transition;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionTransitions;
use SM\Factory\FactoryInterface;

final class StateMachineTransition implements StateMachineTransitionInterface
{
    /** @var FactoryInterface */
    private $subscriptionStateMachineFactory;

    public function __construct(FactoryInterface $subscriptionStateMachineFactory)
    {
        $this->subscriptionStateMachineFactory = $subscriptionStateMachineFactory;
    }

    public function apply(MollieSubscriptionInterface $subscription, string $transitions): void
    {
        $stateMachine = $this->subscriptionStateMachineFactory->get($subscription, MollieSubscriptionTransitions::GRAPH);

        if (!$stateMachine->can($transitions)) {
            return;
        }

        $stateMachine->apply($transitions);
    }
}
