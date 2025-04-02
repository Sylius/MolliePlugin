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
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;
use Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransitionInterface;

final class SubscriptionAndPaymentIdApplicator implements SubscriptionAndPaymentIdApplicatorInterface
{
    public function __construct(
        private readonly MollieApiClient $mollieApiClient,
        private readonly StateMachineTransitionInterface $stateMachineTransition,
        private readonly PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        private readonly ProcessingStateMachineTransitionInterface $processingStateMachineTransition,
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
                $this->paymentStateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN,
                );
                $this->stateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionTransitions::TRANSITION_PROCESS,
                );

                break;
            case PaymentStatus::STATUS_PAID:
                $subscription->resetFailedPaymentCount();
                $this->stateMachineTransition->apply($subscription, MollieSubscriptionTransitions::TRANSITION_ACTIVATE);
                $this->paymentStateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS,
                );
                $this->processingStateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE,
                );

                break;
            default:
                $subscription->incrementFailedPaymentCounter();
                $this->paymentStateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE,
                );

                break;
        }
    }
}
