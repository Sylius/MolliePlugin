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

use SyliusMolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransition;
use SyliusMolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use PhpSpec\ObjectBehavior;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;

final class ProcessingStateMachineTransitionSpec extends ObjectBehavior
{
    function let(FactoryInterface $subscriptionSateMachineFactory): void
    {
        $this->beConstructedWith($subscriptionSateMachineFactory);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ProcessingStateMachineTransition::class);
    }

    function it_should_implement_interface(): void
    {
        $this->shouldImplement(ProcessingStateMachineTransitionInterface::class);
    }

    function it_applies_transition(
        MollieSubscriptionInterface $subscription,
        FactoryInterface $subscriptionSateMachineFactory,
        StateMachineInterface $stateMachine
    ): void {
        $subscriptionSateMachineFactory->get(
            $subscription,
            MollieSubscriptionProcessingTransitions::GRAPH
        )->willReturn($stateMachine);

        $stateMachine->can(MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS)->willReturn(true);
        $stateMachine->apply(MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS)->willReturn(true);

        $this->apply($subscription,MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS);
    }

    function it_cannot_applies_transition(
        MollieSubscriptionInterface $subscription,
        FactoryInterface $subscriptionSateMachineFactory,
        StateMachineInterface $stateMachine
    ): void {
        $subscriptionSateMachineFactory->get(
            $subscription,
            MollieSubscriptionProcessingTransitions::GRAPH
        )->willReturn($stateMachine);

        $stateMachine->can(MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS)->willReturn(false);
        $stateMachine->apply(MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS)->shouldNotBeCalled();

        $this->apply($subscription,MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS);
    }

}
