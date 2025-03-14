<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Action\StateMachine\Applicator;

use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator;
use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator;
use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionTransitions;
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
