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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\PaymentProcessing;

use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use SyliusMolliePlugin\PaymentProcessing\CancelRecurringSubscriptionProcessor;

final class CancelRecurringSubscriptionProcessorTest extends TestCase
{
    private Payum $payumMock;

    private CancelRecurringSubscriptionProcessor $cancelRecurringSubscriptionProcessor;
    protected function setUp(): void
    {
        $this->payumMock = $this->createMock(Payum::class);
        $this->cancelRecurringSubscriptionProcessor = new CancelRecurringSubscriptionProcessor($this->payumMock);
    }

    function testProcessesCancelRecurringSubscription(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);

        $subscriptionMock->expects($this->once())
            ->method('getLastOrder')
            ->willReturn($orderMock)
        ;

        $orderMock->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($paymentMock)
        ;

        $paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($paymentMethodMock)
        ;

        $paymentMethodMock->expects($this->exactly(3))
        ->method('getGatewayConfig')
            ->willReturn($gatewayConfigMock)
        ;

        $gatewayConfigMock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME)
        ;
        $gatewayConfigMock->expects($this->once())
            ->method('getGatewayName')
            ->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME)
        ;

        $this->payumMock->expects($this->once())
            ->method('getGateway')
            ->with(MollieSubscriptionGatewayFactory::FACTORY_NAME)
            ->willReturn($gatewayMock)
        ;

        $gatewayMock->expects($this->once())
            ->method('execute')
        ;

        $this->cancelRecurringSubscriptionProcessor->process($subscriptionMock);
    }

    function testProcessesCancelRecurringSubscriptionWhenLastOrderIsNull(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $subscriptionMock->expects($this->once())->method('getLastOrder')->willReturn(null);
        $gatewayMock->expects($this->never())->method('execute');

        $this->cancelRecurringSubscriptionProcessor->process($subscriptionMock);
    }

    function testProcessesCancelRecurringSubscriptionWhenLastPaymentIsNull(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $subscriptionMock->expects($this->once())->method('getLastOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getLastPayment')->willReturn(null);
        $gatewayMock->expects($this->never())->method('execute');

        $this->cancelRecurringSubscriptionProcessor->process($subscriptionMock);
    }

    function testProcessesCancelRecurringSubscriptionWithNullConfig(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $subscriptionMock->expects($this->once())->method('getLastOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getLastPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getMethod')->willReturn($paymentMethodMock);
        $paymentMethodMock->expects($this->once())->method('getGatewayConfig')->willReturn(null);
        $gatewayMock->expects($this->never())->method('execute');

        $this->cancelRecurringSubscriptionProcessor->process($subscriptionMock);
    }

    function testProcessesCancelRecurringSubscriptionWithWrongFactoryName(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);

        $subscriptionMock->expects($this->once())
            ->method('getLastOrder')
            ->willReturn($orderMock)
        ;

        $orderMock->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($paymentMock)
        ;

        $paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($paymentMethodMock)
        ;

        $paymentMethodMock->expects($this->exactly(2))
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfigMock)
        ;

        $gatewayConfigMock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn('not_mollie_subscription')
        ;

        $gatewayMock->expects($this->never())
            ->method('execute')
        ;

        $this->cancelRecurringSubscriptionProcessor->process($subscriptionMock);
    }
}
