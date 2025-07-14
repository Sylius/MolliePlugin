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

use Mollie\Api\Types\PaymentStatus;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;

final class SubscriptionAndPaymentIdApplicator implements SubscriptionAndPaymentIdApplicatorInterface
{
    public function __construct(
        private readonly MollieApiClient $mollieApiClient,
        private readonly StateMachineInterface $stateMachine,
    ) {
    }

    public function execute(
        MollieSubscriptionInterface $subscription,
        string $paymentId,
    ): void {
        $configuration = $subscription->getSubscriptionConfiguration();
        $payment = $this->mollieApiClient->payments->get($paymentId);

        if (null === $configuration->getMandateId()) {
            $configuration->setMandateId($payment->mandateId);
        }

        if (null === $configuration->getCustomerId()) {
            $configuration->setCustomerId($payment->customerId);
        }

        switch ($payment->status) {
            case PaymentStatus::STATUS_OPEN:
            case PaymentStatus::STATUS_PENDING:
            case PaymentStatus::STATUS_AUTHORIZED:
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
            case PaymentStatus::STATUS_PAID:
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

                break;
        }
    }

    private function apply(MollieSubscriptionInterface $subscription, string $graph, string $transition): void
    {
        if ($this->stateMachine->can($subscription, $graph, $transition)) {
            $this->stateMachine->apply($subscription, $graph, $transition);
        }
    }
}
