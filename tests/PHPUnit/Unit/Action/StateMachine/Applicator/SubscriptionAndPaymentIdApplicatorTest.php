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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Action\StateMachine\Applicator;

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use PHPUnit\Framework\TestCase;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use SyliusMolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;

final class SubscriptionAndPaymentIdApplicatorTest extends TestCase
{
    private MollieApiClient $mollieApiClientMock;

    private StateMachineTransitionInterface $stateMachineTransitionMock;

    private PaymentStateMachineTransitionInterface $paymentStateMachineTransitionMock;

    private ProcessingStateMachineTransitionInterface $processingStateMachineTransitionMock;

    private SubscriptionAndPaymentIdApplicator $subscriptionAndPaymentIdApplicator;

    protected function setUp(): void
    {
        $this->mollieApiClientMock = $this->createMock(MollieApiClient::class);
        $this->stateMachineTransitionMock = $this->createMock(StateMachineTransitionInterface::class);
        $this->paymentStateMachineTransitionMock = $this->createMock(PaymentStateMachineTransitionInterface::class);
        $this->processingStateMachineTransitionMock = $this->createMock(ProcessingStateMachineTransitionInterface::class);
        $this->subscriptionAndPaymentIdApplicator = new SubscriptionAndPaymentIdApplicator($this->mollieApiClientMock, $this->stateMachineTransitionMock, $this->paymentStateMachineTransitionMock, $this->processingStateMachineTransitionMock);
    }

    function testImplementInterface(): void
    {
        $this->assertInstanceOf(SubscriptionAndPaymentIdApplicatorInterface::class, $this->subscriptionAndPaymentIdApplicator);
    }

    function testAppliesTransitionWhenStatusIsOpen(): void
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
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }

    function testAppliesTransitionWhenStatusIsPending(): void
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
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }

    function testAppliesTransitionWhenStatusIsAuthorized(): void
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
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }

    function testAppliesTransitionWhenStatusIsPaid(): void
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
        $this->processingStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE);
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_ACTIVATE);
        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }

    function testAppliesTransitionWhenStatusIsFailure(): void
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
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE);
        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }
}
