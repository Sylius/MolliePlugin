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

namespace Tests\Sylius\MolliePlugin\Unit\Processor;

use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Cloner\SubscriptionOrderClonerInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\PaymentDetailsFactoryInterface;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionProcessor;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionProcessorInterface;

final class SubscriptionProcessorTest extends TestCase
{
    private SubscriptionOrderClonerInterface $orderClonerMock;

    private PaymentFactoryInterface $paymentFactoryMock;

    private OrderRepositoryInterface $orderRepositoryMock;

    private PaymentDetailsFactoryInterface $paymentDetailsFactoryMock;

    private MollieSubscriptionRepositoryInterface $subscriptionRepositoryMock;

    private Payum $paymentRegistryMock;

    private SubscriptionProcessor $subscriptionProcessor;

    protected function setUp(): void
    {
        $this->orderClonerMock = $this->createMock(SubscriptionOrderClonerInterface::class);
        $this->paymentFactoryMock = $this->createMock(PaymentFactoryInterface::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->paymentDetailsFactoryMock = $this->createMock(PaymentDetailsFactoryInterface::class);
        $this->subscriptionRepositoryMock = $this->createMock(MollieSubscriptionRepositoryInterface::class);
        $this->paymentRegistryMock = $this->createMock(Payum::class);
        $this->subscriptionProcessor = new SubscriptionProcessor($this->orderClonerMock, $this->paymentFactoryMock, $this->orderRepositoryMock, $this->paymentDetailsFactoryMock, $this->subscriptionRepositoryMock, $this->paymentRegistryMock);
    }

    public function testImplementSubscriptionProcessorInterface(): void
    {
        $this->assertInstanceOf(SubscriptionProcessorInterface::class, $this->subscriptionProcessor);
    }

    public function testProcessesNextPayment(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $orderItemMock = $this->createMock(OrderItemInterface::class);
        $clonedOrderMock = $this->createMock(OrderInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);
        $lastPaymentMock = $this->createMock(PaymentInterface::class);
        $firstOrderMock = $this->createMock(OrderInterface::class);
        $configurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenMock = $this->createMock(TokenInterface::class);
        $methodMock = $this->createMock(PaymentMethodInterface::class);

        $subscriptionMock->expects($this->once())->method('getOrderItem')->willReturn($orderItemMock);
        $subscriptionMock->expects($this->exactly(2))->method('getFirstOrder')->willReturn($firstOrderMock);
        $subscriptionMock->expects($this->once())->method('getSubscriptionConfiguration')->willReturn($configurationMock);

        $this->orderClonerMock->expects($this->once())
            ->method('clone')
            ->with($subscriptionMock, $firstOrderMock, $orderItemMock)
            ->willReturn($clonedOrderMock)
        ;

        $clonedOrderMock->expects($this->once())->method('getTotal')->willReturn(2);
        $clonedOrderMock->expects($this->exactly(2))->method('getCurrencyCode')->willReturn('EUR');

        $this->paymentFactoryMock->expects($this->once())
            ->method('createWithAmountAndCurrencyCode')
            ->with(2, 'EUR')
            ->willReturn($paymentMock)
        ;

        $firstOrderMock->expects($this->once())
            ->method('getLastPayment')
            ->with(PaymentInterface::STATE_COMPLETED)
            ->willReturn($lastPaymentMock)
        ;

        $methodData = [
            'metadata' => [
                'molliePaymentMethods' => 'test_method',
            ],
        ];
        $lastPaymentMock->expects($this->once())->method('getDetails')->willReturn($methodData);
        $lastPaymentMock->expects($this->once())->method('getMethod')->willReturn($methodMock);

        $paymentMock->expects($this->once())->method('setMethod')->with($methodMock);
        $paymentMock->expects($this->once())->method('setState')->with(PaymentInterface::STATE_NEW);

        $details = [
            'metadata' => [
                'gateway' => 'gateway_id',
            ],
        ];
        $this->paymentDetailsFactoryMock->expects($this->once())
            ->method('createForSubscriptionAndOrder')
            ->with($configurationMock, $clonedOrderMock)
            ->willReturn($details);

        $paymentMock->expects($this->once())->method('setDetails')->with($details);
        $paymentMock->expects($this->once())->method('getDetails')->willReturn($details);

        $clonedOrderMock->expects($this->once())->method('addPayment')->with($paymentMock);

        $this->orderRepositoryMock->expects($this->once())->method('add')->with($clonedOrderMock);

        $subscriptionMock->expects($this->once())->method('addOrder')->with($clonedOrderMock);
        $subscriptionMock->expects($this->once())->method('addPayment')->with($paymentMock);

        $this->subscriptionRepositoryMock->expects($this->once())->method('add')->with($subscriptionMock);

        $this->paymentRegistryMock->expects($this->once())
            ->method('getGateway')
            ->with('gateway_id')
            ->willReturn($gatewayMock)
        ;

        $this->paymentRegistryMock->expects($this->once())
            ->method('getTokenFactory')
            ->willReturn($tokenFactoryMock)
        ;

        $tokenFactoryMock->expects($this->once())
            ->method('createToken')
            ->with('gateway_id', $paymentMock, 'sylius_shop_order_thank_you')
            ->willReturn($tokenMock)
        ;

        $gatewayMock->expects($this->once())
            ->method('execute')
            ->with(new Capture($tokenMock))
        ;

        $this->subscriptionProcessor->processNextPayment($subscriptionMock);
    }

    public function testProcessesNextSubscriptionPayment(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $orderItemMock = $this->createMock(OrderItemInterface::class);
        $clonedOrderMock = $this->createMock(OrderInterface::class);
        $paymentMock = $this->createMock(PaymentInterface::class);
        $lastPaymentMock = $this->createMock(PaymentInterface::class);
        $firstOrderMock = $this->createMock(OrderInterface::class);
        $configurationMock = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenMock = $this->createMock(TokenInterface::class);
        $methodMock = $this->createMock(PaymentMethodInterface::class);

        $subscriptionMock->expects($this->once())->method('getOrderItem')->willReturn($orderItemMock);
        $subscriptionMock->expects($this->exactly(2))->method('getFirstOrder')->willReturn($firstOrderMock); // Ensure this returns a valid OrderInterface mock
        $subscriptionMock->expects($this->once())->method('getSubscriptionConfiguration')->willReturn($configurationMock);

        $this->orderClonerMock->expects($this->once())
            ->method('clone')
            ->with($subscriptionMock, $firstOrderMock, $orderItemMock)
            ->willReturn($clonedOrderMock)
        ;

        $clonedOrderMock->expects($this->once())->method('getTotal')->willReturn(2);
        $clonedOrderMock->expects($this->exactly(2))->method('getCurrencyCode')->willReturn('EUR');

        $this->paymentFactoryMock->expects($this->once())
            ->method('createWithAmountAndCurrencyCode')
            ->with(2, 'EUR')
            ->willReturn($paymentMock)
        ;

        $firstOrderMock->expects($this->once())
            ->method('getLastPayment')
            ->with(PaymentInterface::STATE_COMPLETED)
            ->willReturn($lastPaymentMock)
        ;

        $methodData = [
            'metadata' => [
                'molliePaymentMethods' => 'test_method',
            ],
        ];
        $lastPaymentMock->expects($this->once())->method('getDetails')->willReturn($methodData);
        $lastPaymentMock->expects($this->once())->method('getMethod')->willReturn($methodMock);

        $paymentMock->expects($this->once())->method('setMethod')->with($methodMock);
        $paymentMock->expects($this->once())->method('setState')->with(PaymentInterface::STATE_NEW);

        $details = [
            'metadata' => [
                'gateway' => 'gateway_id',
            ],
        ];
        $this->paymentDetailsFactoryMock->expects($this->once())
            ->method('createForSubscriptionAndOrder')
            ->with($configurationMock, $clonedOrderMock)
            ->willReturn($details)
        ;

        $paymentMock->expects($this->once())->method('setDetails')->with($details);
        $paymentMock->method('getDetails')->willReturn($details);

        $clonedOrderMock->expects($this->once())->method('addPayment')->with($paymentMock);

        $this->orderRepositoryMock->expects($this->once())->method('add')->with($clonedOrderMock);

        $subscriptionMock->expects($this->once())->method('addOrder')->with($clonedOrderMock);
        $subscriptionMock->expects($this->once())->method('addPayment')->with($paymentMock);

        $this->subscriptionRepositoryMock->expects($this->once())->method('add')->with($subscriptionMock);

        $this->paymentRegistryMock
            ->method('getGateway')
            ->with('gateway_id')
            ->willReturn($gatewayMock)
        ;

        $this->paymentRegistryMock
            ->method('getTokenFactory')
            ->willReturn($tokenFactoryMock)
        ;

        $tokenFactoryMock
            ->method('createToken')
            ->with('gateway_id', $paymentMock, 'sylius_shop_order_thank_you')
            ->willReturn($tokenMock)
        ;

        $this->subscriptionProcessor->processNextSubscriptionPayment($subscriptionMock);
    }
}
