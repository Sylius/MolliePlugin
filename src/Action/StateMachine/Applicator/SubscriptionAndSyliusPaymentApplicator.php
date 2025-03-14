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
    public function __construct(private readonly StateMachineTransitionInterface $stateMachineTransition, private readonly PaymentStateMachineTransitionInterface $paymentStateMachineTransition, private readonly ProcessingStateMachineTransitionInterface $processingStateMachineTransition)
    {
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
