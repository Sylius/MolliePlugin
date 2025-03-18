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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Action\StateMachine;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use SyliusMolliePlugin\Action\StateMachine\StatusRecurringSubscriptionAction;
use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Request\StateMachine\StatusRecurringSubscription;
use SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;

final class StatusRecurringSubscriptionActionTest extends TestCase
{
    private EntityManagerInterface $subscriptionManagerMock;

    private SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicatorMock;

    private SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicatorMock;

    private StateMachineTransitionInterface $stateMachineTransition;

    private StatusRecurringSubscriptionAction $statusRecurringSubscriptionAction;

    protected function setUp(): void
    {
        $this->subscriptionManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->subscriptionAndPaymentIdApplicatorMock = $this->createMock(SubscriptionAndPaymentIdApplicatorInterface::class);
        $this->subscriptionAndSyliusPaymentApplicatorMock = $this->createMock(SubscriptionAndSyliusPaymentApplicatorInterface::class);
        $this->stateMachineTransition = $this->createMock(StateMachineTransitionInterface::class);
        $this->statusRecurringSubscriptionAction = new StatusRecurringSubscriptionAction($this->subscriptionManagerMock, $this->subscriptionAndPaymentIdApplicatorMock, $this->subscriptionAndSyliusPaymentApplicatorMock, $this->stateMachineTransition);
    }

    function testAppliesAbortTransition(): void
    {
        $requestMock = $this->createMock(StatusRecurringSubscription::class);
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);

        $requestMock->expects($this->exactly(2))->method('getModel')->willReturn($subscriptionMock);
        $requestMock->expects($this->once())->method('getPaymentId')->willReturn(null);
        $requestMock->expects($this->once())->method('getPayment')->willReturn(null);
        $this->stateMachineTransition->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionTransitions::TRANSITION_COMPLETE],
                [$subscriptionMock, MollieSubscriptionTransitions::TRANSITION_ABORT]
            )
        ;
        $this->subscriptionManagerMock->expects($this->once())->method('persist')->with($subscriptionMock);
        $this->subscriptionManagerMock->expects($this->once())->method('flush');

        $this->statusRecurringSubscriptionAction->execute($requestMock);
    }

    function testExecutesWhenPaymentIdIsNotNull(): void
    {
        $requestMock = $this->createMock(StatusRecurringSubscription::class);
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);

        $requestMock->expects($this->exactly(2))->method('getModel')->willReturn($subscriptionMock);
        $requestMock->expects($this->once())->method('getPaymentId')->willReturn('payment_id');
        $requestMock->expects($this->once())->method('getPayment')->willReturn(null);
        $this->subscriptionAndPaymentIdApplicatorMock->expects($this->once())->method('execute')->with($subscriptionMock, 'payment_id');
        $this->stateMachineTransition->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionTransitions::TRANSITION_COMPLETE],
                [$subscriptionMock, MollieSubscriptionTransitions::TRANSITION_ABORT]
            )
        ;
        $this->subscriptionManagerMock->expects($this->once())->method('persist')->with($subscriptionMock);
        $this->subscriptionManagerMock->expects($this->once())->method('flush');

        $this->statusRecurringSubscriptionAction->execute($requestMock);
    }

    function testExecutesWhenSyliusPaymentIsNotNull(): void
    {
        $requestMock = $this->createMock(StatusRecurringSubscription::class);
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);

        $requestMock->expects($this->exactly(2))->method('getModel')->willReturn($subscriptionMock);
        $requestMock->expects($this->once())->method('getPaymentId')->willReturn(null);
        $requestMock->expects($this->once())->method('getPayment')->willReturn($paymentMock);
        $this->subscriptionAndSyliusPaymentApplicatorMock->expects($this->once())
            ->method('execute')
            ->with($subscriptionMock, $paymentMock)
        ;
        $this->stateMachineTransition->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [$subscriptionMock, MollieSubscriptionTransitions::TRANSITION_COMPLETE],
                [$subscriptionMock, MollieSubscriptionTransitions::TRANSITION_ABORT]
            )
        ;
        $this->subscriptionManagerMock->expects($this->once())->method('persist')->with($subscriptionMock);
        $this->subscriptionManagerMock->expects($this->once())->method('flush');

        $this->statusRecurringSubscriptionAction->execute($requestMock);
    }

    function testSupportsStatusRecurringSubscriptionRequestAndSubscriptionModel(): void
    {
        $requestMock = $this->createMock(StatusRecurringSubscription::class);
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);

        $requestMock->expects($this->once())->method('getModel')->willReturn($subscriptionMock);
        $this->assertTrue($this->statusRecurringSubscriptionAction->supports($requestMock));
    }
}
