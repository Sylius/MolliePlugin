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
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionTransitions;
use Mollie\Api\Types\PaymentStatus;

final class SubscriptionAndPaymentIdApplicator implements SubscriptionAndPaymentIdApplicatorInterface
{
    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var StateMachineTransitionInterface */
    private $stateMachineTransition;

    /** @var PaymentStateMachineTransitionInterface */
    private $paymentStateMachineTransition;

    /** @var ProcessingStateMachineTransitionInterface */
    private $processingStateMachineTransition;

    public function __construct(
        MollieApiClient $mollieApiClient,
        StateMachineTransitionInterface $stateMachineTransition,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        ProcessingStateMachineTransitionInterface $processingStateMachineTransition
    ) {
        $this->mollieApiClient = $mollieApiClient;
        $this->stateMachineTransition = $stateMachineTransition;
        $this->paymentStateMachineTransition = $paymentStateMachineTransition;
        $this->processingStateMachineTransition = $processingStateMachineTransition;
    }

    public function execute(
        MollieSubscriptionInterface $subscription,
        string $paymentId
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
                    MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
                );
                $this->stateMachineTransition->apply(
                    $subscription,
                    MollieSubscriptionTransitions::TRANSITION_PROCESS
                )
                ;

                break;
            case PaymentStatus::STATUS_PAID:
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

                break;
        }
    }
}
