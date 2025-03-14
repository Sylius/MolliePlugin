<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\StateMachine\Applicator;

use Sylius\MolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionTransitions;
use Sylius\Component\Core\Model\PaymentInterface;

final class SubscriptionAndSyliusPaymentApplicator implements SubscriptionAndSyliusPaymentApplicatorInterface
{
    /** @var StateMachineTransitionInterface */
    private $stateMachineTransition;

    /** @var PaymentStateMachineTransitionInterface */
    private $paymentStateMachineTransition;

    /** @var ProcessingStateMachineTransitionInterface */
    private $processingStateMachineTransition;

    public function __construct(
        StateMachineTransitionInterface $stateMachineTransition,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        ProcessingStateMachineTransitionInterface $processingStateMachineTransition
    ) {
        $this->stateMachineTransition = $stateMachineTransition;
        $this->paymentStateMachineTransition = $paymentStateMachineTransition;
        $this->processingStateMachineTransition = $processingStateMachineTransition;
    }

    public function execute(
        MollieSubscriptionInterface $subscription,
        PaymentInterface $payment
    ): void {
        switch ($payment->getState()) {
            case PaymentInterface::STATE_NEW:
            case PaymentInterface::STATE_PROCESSING:
            case PaymentInterface::STATE_AUTHORIZED:
            case PaymentInterface::STATE_CART:
                $this->paymentStateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
                );
                $this->stateMachineTransition->apply($subscription, MollieSubscriptionTransitions::TRANSITION_PROCESS);

                break;
            case PaymentInterface::STATE_COMPLETED:
                $subscription->resetFailedPaymentCount();
                $this->stateMachineTransition->apply($subscription, MollieSubscriptionTransitions::TRANSITION_ACTIVATE);
                $this->paymentStateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS
                );
                $this->processingStateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE
                );

                break;
            default:
                $subscription->incrementFailedPaymentCounter();
                $this->paymentStateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE
                );
        }
    }
}
