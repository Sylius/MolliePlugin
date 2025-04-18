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
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;
use Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransitionInterface;

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
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
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
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
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
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_PROCESS);
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
        $this->processingStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE);
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS);
        $this->stateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionTransitions::TRANSITION_ACTIVATE);
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
        $this->paymentStateMachineTransitionMock->expects($this->once())->method('apply')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE);
        $this->subscriptionAndPaymentIdApplicator->execute($subscriptionMock, 'id_1');
    }
}
