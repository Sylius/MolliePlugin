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

namespace spec\Sylius\MolliePlugin\Action\StateMachine\Applicator;

use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator;
use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransition;
use Sylius\MolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionTransitions;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use PhpSpec\ObjectBehavior;

final class SubscriptionAndPaymentIdApplicatorSpec extends ObjectBehavior
{
    function let(
        MollieApiClient $mollieApiClient,
        StateMachineTransitionInterface $stateMachineTransition,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        ProcessingStateMachineTransitionInterface $processingStateMachineTransition
    ): void {
        $this->beConstructedWith(
            $mollieApiClient,
            $stateMachineTransition,
            $paymentStateMachineTransition,
            $processingStateMachineTransition
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(SubscriptionAndPaymentIdApplicator::class);
    }

    function it_should_implement_interface(): void
    {
        $this->shouldImplement(SubscriptionAndPaymentIdApplicatorInterface::class);
    }

    function it_applies_transition_when_status_is_open(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = PaymentStatus::STATUS_OPEN;

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }

    function it_applies_transition_when_status_is_pending(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = PaymentStatus::STATUS_PENDING;

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }

    function it_applies_transition_when_status_is_authorized(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = PaymentStatus::STATUS_AUTHORIZED;

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }

    function it_applies_transition_when_status_is_paid(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition,
        ProcessingStateMachineTransitionInterface $processingStateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = PaymentStatus::STATUS_PAID;

        $subscription->resetFailedPaymentCount()->shouldBeCalled();

        $processingStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE
        )->shouldBeCalled();
        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ACTIVATE
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }

    function it_applies_transition_when_status_is_failure(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = 'definitely not payment status';

        $subscription->incrementFailedPaymentCounter()->shouldBeCalled();

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }
}
