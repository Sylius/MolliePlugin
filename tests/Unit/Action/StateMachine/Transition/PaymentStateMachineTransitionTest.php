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

namespace Tests\Sylius\MolliePlugin\Unit\Action\StateMachine\Transition;

use PHPUnit\Framework\TestCase;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition;
use Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface;

final class PaymentStateMachineTransitionTest extends TestCase
{
    private FactoryInterface $subscriptionSateMachineFactoryMock;

    private PaymentStateMachineTransition $paymentStateMachineTransition;

    protected function setUp(): void
    {
        $this->subscriptionSateMachineFactoryMock = $this->createMock(FactoryInterface::class);
        $this->paymentStateMachineTransition = new PaymentStateMachineTransition($this->subscriptionSateMachineFactoryMock);
    }

    public function testImplementInterface(): void
    {
        $this->assertInstanceOf(PaymentStateMachineTransitionInterface::class, $this->paymentStateMachineTransition);
    }

    public function testAppliesTransition(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $stateMachineMock = $this->createMock(StateMachineInterface::class);

        $this->subscriptionSateMachineFactoryMock->expects($this->once())->method('get')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH)->willReturn($stateMachineMock);
        $stateMachineMock->expects($this->once())->method('can')->with(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(true);
        $stateMachineMock->expects($this->once())->method('apply')->with(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(true);
        $this->paymentStateMachineTransition->apply($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
    }

    public function testCannotAppliesTransition(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $stateMachineMock = $this->createMock(StateMachineInterface::class);

        $this->subscriptionSateMachineFactoryMock->expects($this->once())->method('get')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH)->willReturn($stateMachineMock);
        $stateMachineMock->expects($this->once())->method('can')->with(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(false);
        $stateMachineMock->expects($this->never())->method('apply')->with(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->paymentStateMachineTransition->apply($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
    }
}
