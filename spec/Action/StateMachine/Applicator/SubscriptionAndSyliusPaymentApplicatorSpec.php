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

namespace spec\SyliusMolliePlugin\Action\StateMachine\Applicator;

use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use SyliusMolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;

final class SubscriptionAndSyliusPaymentApplicatorSpec extends ObjectBehavior
{
    function let(
        StateMachineTransitionInterface $stateMachineTransition,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        ProcessingStateMachineTransitionInterface $processingStateMachineTransition
    ): void {
        $this->beConstructedWith(
            $stateMachineTransition,
            $paymentStateMachineTransition,
            $processingStateMachineTransition
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(SubscriptionAndSyliusPaymentApplicator::class);
    }

    function it_should_implement_interface(): void
    {
        $this->shouldImplement(SubscriptionAndSyliusPaymentApplicatorInterface::class);
    }

    function it_applies_transition_when_status_is_new(
        MollieSubscriptionInterface $subscription,
        PaymentInterface $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $payment->getState()->willReturn(PaymentInterface::STATE_NEW);

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply($subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, $payment);
    }

    function it_applies_transition_when_status_is_processing(
        MollieSubscriptionInterface $subscription,
        PaymentInterface $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $payment->getState()->willReturn(PaymentInterface::STATE_PROCESSING);

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply($subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, $payment);
    }

    function it_applies_transition_when_status_is_authorized(
        MollieSubscriptionInterface $subscription,
        PaymentInterface $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $payment->getState()->willReturn(PaymentInterface::STATE_AUTHORIZED);

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply($subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, $payment);
    }

    function it_applies_transition_when_status_is_cart(
        MollieSubscriptionInterface $subscription,
        PaymentInterface $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $payment->getState()->willReturn(PaymentInterface::STATE_CART);

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply($subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, $payment);
    }

    function it_applies_transition_when_status_is_completed(
        MollieSubscriptionInterface $subscription,
        PaymentInterface $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition,
        ProcessingStateMachineTransitionInterface $processingStateMachineTransition
    ): void {
        $payment->getState()->willReturn(PaymentInterface::STATE_COMPLETED);

        $subscription->resetFailedPaymentCount()->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ACTIVATE
        )->shouldBeCalled();
        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS
        )->shouldBeCalled();
        $processingStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE
        )->shouldBeCalled();

        $this->execute($subscription, $payment);
    }

    function it_applies_transition_when_status_is_paid(
        MollieSubscriptionInterface $subscription,
        PaymentInterface $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition
    ): void {
        $payment->getState()->willReturn('definitely not state');

        $subscription->incrementFailedPaymentCounter()->shouldBeCalled();
        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE
        )->shouldBeCalled();

        $this->execute($subscription, $payment);
    }
}
