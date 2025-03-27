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

namespace Sylius\MolliePlugin\StateMachine\Transition;

use SM\Factory\FactoryInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;

final class StateMachineTransition implements StateMachineTransitionInterface
{
    public function __construct(private readonly FactoryInterface $subscriptionStateMachineFactory)
    {
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
