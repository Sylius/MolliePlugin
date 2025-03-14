<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Action\StateMachine;

use Sylius\MolliePlugin\Action\Api\BaseApiAwareAction;
use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use Sylius\MolliePlugin\Action\StateMachine\StatusRecurringSubscriptionAction;
use Sylius\MolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Request\Api\CancelRecurringSubscription;
use Sylius\MolliePlugin\Request\StateMachine\StatusRecurringSubscription;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionTransitions;
use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Endpoints\CustomerEndpoint;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use PhpSpec\ObjectBehavior;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusRecurringSubscriptionActionSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $subscriptionManager,
        SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator,
        SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $this->beConstructedWith(
            $subscriptionManager,
            $subscriptionAndPaymentIdApplicator,
            $subscriptionAndSyliusPaymentApplicator,
            $stateMachineTransition
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(StatusRecurringSubscriptionAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_api_aware_interface(): void
    {
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    function it_extends_base_api_aware(): void
    {
        $this->shouldHaveType(BaseApiAwareAction::class);
    }

    function it_applies_abort_tranistion(
        StatusRecurringSubscription $request,
        MollieSubscriptionInterface $subscription,
        EntityManagerInterface $subscriptionManager,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $request->getModel()->willReturn($subscription);

        $request->getPaymentId()->willReturn(null);
        $request->getPayment()->willReturn(null);

        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_COMPLETE
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ABORT
        )->shouldBeCalled();

        $subscriptionManager->persist($subscription)->shouldBeCalled();
        $subscriptionManager->flush()->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_when_payment_id_is_not_null(
        StatusRecurringSubscription $request,
        MollieSubscriptionInterface $subscription,
        EntityManagerInterface $subscriptionManager,
        StateMachineTransitionInterface $stateMachineTransition,
        SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator
    ): void {
        $request->getModel()->willReturn($subscription);

        $request->getPaymentId()->willReturn('payment_id');
        $request->getPayment()->willReturn(null);

        $subscriptionAndPaymentIdApplicator->execute($subscription, 'payment_id')->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_COMPLETE
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ABORT
        )->shouldBeCalled();

        $subscriptionManager->persist($subscription)->shouldBeCalled();
        $subscriptionManager->flush()->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_when_sylius_payment_is_not_null(
        StatusRecurringSubscription $request,
        MollieSubscriptionInterface $subscription,
        EntityManagerInterface $subscriptionManager,
        StateMachineTransitionInterface $stateMachineTransition,
        SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator,
        PaymentInterface $payment
    ): void {
        $request->getModel()->willReturn($subscription);

        $request->getPaymentId()->willReturn(null);
        $request->getPayment()->willReturn($payment);

        $subscriptionAndSyliusPaymentApplicator->execute($subscription, $payment)->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_COMPLETE
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ABORT
        )->shouldBeCalled();

        $subscriptionManager->persist($subscription)->shouldBeCalled();
        $subscriptionManager->flush()->shouldBeCalled();

        $this->execute($request);
    }

    function it_supports_status_recurring_subscription_request_and_subscription_model(
        StatusRecurringSubscription $request,
        MollieSubscriptionInterface $subscription
    ): void {
        $request->getModel()->willReturn($subscription);

        $this->supports($request)->shouldReturn(true);
    }
}
