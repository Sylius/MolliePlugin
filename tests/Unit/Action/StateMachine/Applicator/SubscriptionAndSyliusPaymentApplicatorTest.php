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
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;

final class SubscriptionAndSyliusPaymentApplicatorTest extends TestCase
{
    private StateMachineInterface $stateMachineMock;

    private SubscriptionAndSyliusPaymentApplicator $subscriptionAndSyliusPaymentApplicator;

    protected function setUp(): void
    {
        $this->stateMachineMock = $this->createMock(StateMachineInterface::class);
        $this->subscriptionAndSyliusPaymentApplicator = new SubscriptionAndSyliusPaymentApplicator($this->stateMachineMock);
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

        $this->stateMachineMock->method('can')
            ->willReturnMap([
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN, true],
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_PROCESS, true],
            ])
        ;

        $this->stateMachineMock->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN],
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_PROCESS],
            )
        ;

        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsProcessing(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_PROCESSING);

        $this->stateMachineMock->method('can')
            ->willReturnMap([
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN, true],
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_PROCESS, true],
            ])
        ;

        $this->stateMachineMock->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN],
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_PROCESS],
            )
        ;

        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsAuthorized(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_AUTHORIZED);

        $this->stateMachineMock->method('can')
            ->willReturnMap([
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN, true],
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_PROCESS, true],
            ])
        ;

        $this->stateMachineMock->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN],
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_PROCESS],
            )
        ;

        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsCart(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_CART);

        $this->stateMachineMock->method('can')
            ->willReturnMap([
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN, true],
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_PROCESS, true],
            ])
        ;

        $this->stateMachineMock->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN],
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_PROCESS],
            )
        ;

        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsCompleted(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn(PaymentInterface::STATE_COMPLETED);
        $subscriptionMock->expects($this->once())->method('resetFailedPaymentCount');

        $this->stateMachineMock->method('can')
            ->willReturnMap([
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_ACTIVATE, true],
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS, true],
                [$subscriptionMock, MollieSubscriptionProcessingTransitions::GRAPH, MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE, true],
            ])
        ;

        $this->stateMachineMock->expects($this->exactly(3))
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionTransitions::GRAPH, MollieSubscriptionTransitions::TRANSITION_ACTIVATE],
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS],
                [$subscriptionMock, MollieSubscriptionProcessingTransitions::GRAPH, MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE],
            )
        ;

        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }

    public function testAppliesTransitionWhenStatusIsPaid(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $paymentMock->expects($this->once())->method('getState')->willReturn('definitely not state');
        $subscriptionMock->expects($this->once())->method('incrementFailedPaymentCounter');

        $this->stateMachineMock->method('can')
            ->willReturnMap([
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE, true],
            ])
        ;

        $this->stateMachineMock->expects($this->once())
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE],
            )
        ;

        $this->subscriptionAndSyliusPaymentApplicator->execute($subscriptionMock, $paymentMock);
    }
}
