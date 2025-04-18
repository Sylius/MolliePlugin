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
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransition;
use Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransitionInterface;

final class ProcessingStateMachineTransitionTest extends TestCase
{
    private FactoryInterface $subscriptionSateMachineFactoryMock;

    private ProcessingStateMachineTransition $processingStateMachineTransition;

    protected function setUp(): void
    {
        $this->subscriptionSateMachineFactoryMock = $this->createMock(FactoryInterface::class);
        $this->processingStateMachineTransition = new ProcessingStateMachineTransition($this->subscriptionSateMachineFactoryMock);
    }

    public function testImplementInterface(): void
    {
        $this->assertInstanceOf(ProcessingStateMachineTransitionInterface::class, $this->processingStateMachineTransition);
    }

    public function testAppliesTransition(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $stateMachineMock = $this->createMock(StateMachineInterface::class);

        $this->subscriptionSateMachineFactoryMock->expects($this->once())->method('get')->with($subscriptionMock, MollieSubscriptionProcessingTransitions::GRAPH)->willReturn($stateMachineMock);
        $stateMachineMock->expects($this->once())->method('can')->with(MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS)->willReturn(true);
        $stateMachineMock->expects($this->once())->method('apply')->with(MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS)->willReturn(true);
        $this->processingStateMachineTransition->apply($subscriptionMock, MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS);
    }

    public function testCannotAppliesTransition(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $stateMachineMock = $this->createMock(StateMachineInterface::class);

        $this->subscriptionSateMachineFactoryMock->expects($this->once())->method('get')->with($subscriptionMock, MollieSubscriptionProcessingTransitions::GRAPH)->willReturn($stateMachineMock);
        $stateMachineMock->expects($this->once())->method('can')->with(MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS)->willReturn(false);
        $stateMachineMock->expects($this->never())->method('apply')->with(MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS);
        $this->processingStateMachineTransition->apply($subscriptionMock, MollieSubscriptionProcessingTransitions::TRANSITION_PROCESS);
    }
}
