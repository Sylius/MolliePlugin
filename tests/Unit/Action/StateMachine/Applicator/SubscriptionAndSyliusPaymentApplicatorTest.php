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

namespace Tests\Sylius\MolliePlugin\Unit\Action\StateMachine\Applicator;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;
use Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransitionInterface;

final class SubscriptionAndSyliusPaymentApplicatorTest extends TestCase
{
    private StateMachineTransitionInterface $stateMachineTransitionMock;

    private PaymentStateMachineTransitionInterface $paymentStateMachineTransitionMock;

    private ProcessingStateMachineTransitionInterface $processingStateMachineTransitionMock;

    private SubscriptionAndSyliusPaymentApplicator $subscriptionAndSyliusPaymentApplicator;

    protected function setUp(): void
    {
        $this->stateMachineTransitionMock = $this->createMock(StateMachineTransitionInterface::class);
        $this->paymentStateMachineTransitionMock = $this->createMock(PaymentStateMachineTransitionInterface::class);
        $this->processingStateMachineTransitionMock = $this->createMock(ProcessingStateMachineTransitionInterface::class);
        $this->subscriptionAndSyliusPaymentApplicator = new SubscriptionAndSyliusPaymentApplicator($this->stateMachineTransitionMock, $this->paymentStateMachineTransitionMock, $this->processingStateMachineTransitionMock);
    }

    public function testImplementInterface(): void
    {
        $this->assertInstanceOf(SubscriptionAndSyliusPaymentApplicatorInterface::class, $this->subscriptionAndSyliusPaymentApplicator);
    }

    public function testAppliesTransitionWhenStatusIsNew(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_NEW);
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsProcessing(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_PROCESSING);
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsAuthorized(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_AUTHORIZED);
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsCart(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_CART);
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsCompleted(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_COMPLETED);
        $subscriptionMock->expects($this->once())->method('resetFailedPaymentCount');
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_ACTIVATE);
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS);
        $this->processingStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE);
        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsPaid(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn('definitely not state');
        $subscriptionMock->expects($this->once())->method('incrementFailedPaymentCounter');
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE);
        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }
}
