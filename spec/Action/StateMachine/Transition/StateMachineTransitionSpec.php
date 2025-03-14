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

namespace spec\SyliusMolliePlugin\Action\StateMachine\Transition;

use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransition;
use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;
use PhpSpec\ObjectBehavior;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;

final class StateMachineTransitionSpec extends ObjectBehavior
{
    function let(FactoryInterface $subscriptionSateMachineFactory): void
    {
        $this->beConstructedWith($subscriptionSateMachineFactory);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(StateMachineTransition::class);
    }

    function it_should_implement_interface(): void
    {
        $this->shouldImplement(StateMachineTransitionInterface::class);
    }

    function it_applies_transition(
        MollieSubscriptionInterface $subscription,
        FactoryInterface $subscriptionSateMachineFactory,
        StateMachineInterface $stateMachine
    ): void {
        $subscriptionSateMachineFactory->get(
            $subscription,
            MollieSubscriptionTransitions::GRAPH
        )->willReturn($stateMachine);

        $stateMachine->can(MollieSubscriptionTransitions::TRANSITION_COMPLETE)->willReturn(true);
        $stateMachine->apply(MollieSubscriptionTransitions::TRANSITION_COMPLETE)->willReturn(true);

        $this->apply($subscription,MollieSubscriptionTransitions::TRANSITION_COMPLETE);
    }

    function it_cannot_applies_transition(
        MollieSubscriptionInterface $subscription,
        FactoryInterface $subscriptionSateMachineFactory,
        StateMachineInterface $stateMachine
    ): void {
        $subscriptionSateMachineFactory->get(
            $subscription,
            MollieSubscriptionTransitions::GRAPH
        )->willReturn($stateMachine);

        $stateMachine->can(MollieSubscriptionTransitions::TRANSITION_COMPLETE)->willReturn(false);
        $stateMachine->apply(MollieSubscriptionTransitions::TRANSITION_COMPLETE)->shouldNotBeCalled();

        $this->apply($subscription,MollieSubscriptionTransitions::TRANSITION_COMPLETE);
    }
}
