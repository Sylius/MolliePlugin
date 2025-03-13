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

namespace SyliusMolliePlugin\Action\StateMachine\Applicator;

use SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use SyliusMolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;
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
