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

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;

final class SubscriptionAndPaymentIdApplicatorTest extends TestCase
{
    private MollieApiClient $mollieApiClientMock;

    private StateMachineInterface $stateMachineMock;

    private SubscriptionAndPaymentIdApplicator $subscriptionAndPaymentIdApplicator;

    protected function setUp(): void
    {
        $this->mollieApiClientMock = $this->createMock(MollieApiClient::class);
        $this->stateMachineMock = $this->createMock(StateMachineInterface::class);
        $this->subscriptionAndPaymentIdApplicator = new SubscriptionAndPaymentIdApplicator($this->mollieApiClientMock, $this->stateMachineMock);
    }

    public function testImplementInterface(): void
    {
        $this->assertInstanceOf(SubscriptionAndPaymentIdApplicatorInterface::class, $this->subscriptionAndPaymentIdApplicator);
    }

    public function testAppliesTransitionWhenStatusIsOpen(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $configurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $paymentEndpointMock = $this->createMock(PaymentEndpoint::class);
        $paymentMock = $this->createMock(Payment::class);

        $subscriptionMock->expects($this->once())->method('getSubscriptionConfiguration')->willReturn($configurationMock);
        $this->mollieApiClientMock->payments = $paymentEndpointMock;
        $paymentEndpointMock->expects($this->once())->method('get')->with('id_1')->willReturn($paymentMock);
        $configurationMock->expects($this->once())->method('getMandateId')->willReturn(null);
        $configurationMock->expects($this->once())->method('getCustomerId')->willReturn(null);
        $paymentMock->mandateId = 'mandate_id';
        $paymentMock->customerId = 'customer_id';
        $configurationMock->expects($this->once())->method('setMandateId')->with('mandate_id');
        $configurationMock->expects($this->once())->method('setCustomerId')->with('customer_id');
        $paymentMock->status = PaymentStatus::STATUS_OPEN;

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

        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }

    public function testAppliesTransitionWhenStatusIsPending(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $configurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $paymentEndpointMock = $this->createMock(PaymentEndpoint::class);
        $paymentMock = $this->createMock(Payment::class);

        $subscriptionMock->expects($this->once())->method('getSubscriptionConfiguration')->willReturn($configurationMock);
        $this->mollieApiClientMock->payments = $paymentEndpointMock;
        $paymentEndpointMock->expects($this->once())->method('get')->with('id_1')->willReturn($paymentMock);
        $configurationMock->expects($this->once())->method('getMandateId')->willReturn(null);
        $configurationMock->expects($this->once())->method('getCustomerId')->willReturn(null);
        $paymentMock->mandateId = 'mandate_id';
        $paymentMock->customerId = 'customer_id';
        $configurationMock->expects($this->once())->method('setMandateId')->with('mandate_id');
        $configurationMock->expects($this->once())->method('setCustomerId')->with('customer_id');
        $paymentMock->status = PaymentStatus::STATUS_PENDING;

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

        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }

    public function testAppliesTransitionWhenStatusIsAuthorized(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $configurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $paymentEndpointMock = $this->createMock(PaymentEndpoint::class);
        $paymentMock = $this->createMock(Payment::class);

        $subscriptionMock->expects($this->once())->method('getSubscriptionConfiguration')->willReturn($configurationMock);
        $this->mollieApiClientMock->payments = $paymentEndpointMock;
        $paymentEndpointMock->expects($this->once())->method('get')->with('id_1')->willReturn($paymentMock);
        $configurationMock->expects($this->once())->method('getMandateId')->willReturn(null);
        $configurationMock->expects($this->once())->method('getCustomerId')->willReturn(null);
        $paymentMock->mandateId = 'mandate_id';
        $paymentMock->customerId = 'customer_id';
        $configurationMock->expects($this->once())->method('setMandateId')->with('mandate_id');
        $configurationMock->expects($this->once())->method('setCustomerId')->with('customer_id');
        $paymentMock->status = PaymentStatus::STATUS_AUTHORIZED;

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

        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }

    public function testAppliesTransitionWhenStatusIsPaid(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $configurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $paymentEndpointMock = $this->createMock(PaymentEndpoint::class);
        $paymentMock = $this->createMock(Payment::class);

        $subscriptionMock->expects($this->once())->method('getSubscriptionConfiguration')->willReturn($configurationMock);
        $this->mollieApiClientMock->payments = $paymentEndpointMock;
        $paymentEndpointMock->expects($this->once())->method('get')->with('id_1')->willReturn($paymentMock);
        $configurationMock->expects($this->once())->method('getMandateId')->willReturn(null);
        $configurationMock->expects($this->once())->method('getCustomerId')->willReturn(null);
        $paymentMock->mandateId = 'mandate_id';
        $paymentMock->customerId = 'customer_id';
        $configurationMock->expects($this->once())->method('setMandateId')->with('mandate_id');
        $configurationMock->expects($this->once())->method('setCustomerId')->with('customer_id');
        $paymentMock->status = PaymentStatus::STATUS_PAID;
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

        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }

    public function testAppliesTransitionWhenStatusIsFailure(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $configurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $paymentEndpointMock = $this->createMock(PaymentEndpoint::class);
        $paymentMock = $this->createMock(Payment::class);

        $subscriptionMock->expects($this->once())->method('getSubscriptionConfiguration')->willReturn($configurationMock);
        $this->mollieApiClientMock->payments = $paymentEndpointMock;
        $paymentEndpointMock->expects($this->once())->method('get')->with('id_1')->willReturn($paymentMock);
        $configurationMock->expects($this->once())->method('getMandateId')->willReturn(null);
        $configurationMock->expects($this->once())->method('getCustomerId')->willReturn(null);
        $paymentMock->mandateId = 'mandate_id';
        $paymentMock->customerId = 'customer_id';
        $configurationMock->expects($this->once())->method('setMandateId')->with('mandate_id');
        $configurationMock->expects($this->once())->method('setCustomerId')->with('customer_id');
        $paymentMock->status = 'definitely not payment status';
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

        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }
}
