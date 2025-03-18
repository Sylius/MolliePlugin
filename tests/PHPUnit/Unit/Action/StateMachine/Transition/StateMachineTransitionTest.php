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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Action\StateMachine\Transition;

use PHPUnit\Framework\TestCase;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransition;
use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;

final class StateMachineTransitionTest extends TestCase
{
    private FactoryInterface $subscriptionSateMachineFactoryMock;

    private StateMachineTransition $stateMachineTransition;

    protected function setUp(): void
    {
        $this->subscriptionSateMachineFactoryMock = $this->createMock(FactoryInterface::class);
        $this->stateMachineTransition = new StateMachineTransition($this->subscriptionSateMachineFactoryMock);
    }

    function testImplementInterface(): void
    {
        $this->assertInstanceOf(StateMachineTransitionInterface::class, $this->stateMachineTransition);
    }

    function testAppliesTransition(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $stateMachineMock = $this->createMock(StateMachineInterface::class);

        $this->subscriptionSateMachineFactoryMock->expects($this->once())->method('get')->with($subscriptionMock, MollieSubscriptionTransitions::GRAPH)->willReturn($stateMachineMock);
        $stateMachineMock->expects($this->once())->method('can')->with(MollieSubscriptionTransitions::TRANSITION_COMPLETE)->willReturn(true);
        $stateMachineMock->expects($this->once())->method('apply')->with(MollieSubscriptionTransitions::TRANSITION_COMPLETE)->willReturn(true);
        $this->stateMachineTransition->apply($subscriptionMock,MollieSubscriptionTransitions::TRANSITION_COMPLETE);
    }

    function testCannotAppliesTransition(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $stateMachineMock = $this->createMock(StateMachineInterface::class);

        $this->subscriptionSateMachineFactoryMock->expects($this->once())->method('get')->with($subscriptionMock, MollieSubscriptionTransitions::GRAPH)->willReturn($stateMachineMock);
        $stateMachineMock->expects($this->once())->method('can')->with(MollieSubscriptionTransitions::TRANSITION_COMPLETE)->willReturn(false);
        $stateMachineMock->expects($this->never())->method('apply')->with(MollieSubscriptionTransitions::TRANSITION_COMPLETE);
        $this->stateMachineTransition->apply($subscriptionMock,MollieSubscriptionTransitions::TRANSITION_COMPLETE);
    }
}
