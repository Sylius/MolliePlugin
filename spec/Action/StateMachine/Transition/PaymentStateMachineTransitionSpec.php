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

use SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransition;
use SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use PhpSpec\ObjectBehavior;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;

final class PaymentStateMachineTransitionSpec extends ObjectBehavior
{
    function let(FactoryInterface $subscriptionSateMachineFactory): void
    {
        $this->beConstructedWith($subscriptionSateMachineFactory);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(PaymentStateMachineTransition::class);
    }

    function it_should_implement_interface(): void
    {
        $this->shouldImplement(PaymentStateMachineTransitionInterface::class);
    }

    function it_applies_transition(
        MollieSubscriptionInterface $subscription,
        FactoryInterface $subscriptionSateMachineFactory,
        StateMachineInterface $stateMachine
    ): void {
        $subscriptionSateMachineFactory->get(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::GRAPH
        )->willReturn($stateMachine);

        $stateMachine->can(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(true);
        $stateMachine->apply(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(true);

        $this->apply($subscription,MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
    }

    function it_cannot_applies_transition(
        MollieSubscriptionInterface $subscription,
        FactoryInterface $subscriptionSateMachineFactory,
        StateMachineInterface $stateMachine
    ): void {
        $subscriptionSateMachineFactory->get(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::GRAPH
        )->willReturn($stateMachine);

        $stateMachine->can(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(false);
        $stateMachine->apply(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->shouldNotBeCalled();

        $this->apply($subscription,MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
    }
}
