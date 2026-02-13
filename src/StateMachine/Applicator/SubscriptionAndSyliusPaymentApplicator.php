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

namespace Sylius\MolliePlugin\StateMachine\Applicator;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;

final class SubscriptionAndSyliusPaymentApplicator implements SubscriptionAndSyliusPaymentApplicatorInterface
{
    public function __construct(
        private readonly StateMachineInterface $stateMachine,
    ) {
    }

    public function execute(
        MollieSubscriptionInterface $subscription,
        PaymentInterface $payment,
    ): void {
        switch ($payment->getState()) {
            case PaymentInterface::STATE_NEW:
            case PaymentInterface::STATE_PROCESSING:
            case PaymentInterface::STATE_AUTHORIZED:
            case PaymentInterface::STATE_CART:
                $this->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::GRAPH,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN,
                );
                $this->apply(
                    $subscription,
                    MollieSubscriptionTransitions::GRAPH,
                    MollieSubscriptionTransitions::TRANSITION_PROCESS,
                );

                break;
            case PaymentInterface::STATE_COMPLETED:
                $subscription->resetFailedPaymentCount();
                $this->apply(
                    $subscription,
                    MollieSubscriptionTransitions::GRAPH,
                    MollieSubscriptionTransitions::TRANSITION_ACTIVATE,
                );
                $this->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::GRAPH,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS,
                );
                $this->apply(
                    $subscription,
                    MollieSubscriptionProcessingTransitions::GRAPH,
                    MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE,
                );

                break;
            default:
                $subscription->incrementFailedPaymentCounter();
                $this->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::GRAPH,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE,
                );
        }
    }

    private function apply(MollieSubscriptionInterface $subscription, string $graph, string $transition): void
    {
        if ($this->stateMachine->can($subscription, $graph, $transition)) {
            $this->stateMachine->apply($subscription, $graph, $transition);
        }
    }
}
