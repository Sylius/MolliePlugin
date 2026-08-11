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

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\PaymentSurchargeCalculatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Processor\PaymentSurchargeProcessor;

final class PaymentSurchargeProcessorTest extends TestCase
{
    private PaymentSurchargeCalculatorInterface $calculatorMock;

    private PaymentSurchargeProcessor $processor;

    protected function setUp(): void
    {
        $this->calculatorMock = $this->createMock(PaymentSurchargeCalculatorInterface::class);
        $this->processor = new PaymentSurchargeProcessor($this->calculatorMock);
    }

    public function testImplementsOrderProcessorInterface(): void
    {
        $this->assertInstanceOf(OrderProcessorInterface::class, $this->processor);
    }

    public function testDoesNothingWhenOrderCannotBeProcessed(): void
    {
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->expects($this->once())->method('canBeProcessed')->willReturn(false);
        $orderMock->expects($this->never())->method('getLastPayment');

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($orderMock);
    }

    public function testDoesNothingWhenOrderHasNoPayment(): void
    {
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->method('canBeProcessed')->willReturn(true);
        $orderMock->expects($this->once())->method('getLastPayment')->willReturn(null);

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($orderMock);
    }

    public function testDoesNothingWhenPaymentHasNoMethod(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn(null);
        $paymentMock->expects($this->never())->method('getDetails');

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($this->createOrderWithPayment($paymentMock));
    }

    public function testDoesNothingWhenPaymentMethodHasNoGatewayConfig(): void
    {
        $paymentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $paymentMethodMock->method('getGatewayConfig')->willReturn(null);

        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn($paymentMethodMock);
        $paymentMock->expects($this->never())->method('getDetails');

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($this->createOrderWithPayment($paymentMock));
    }

    public function testDoesNothingWhenFactoryNameBelongsToAnotherGateway(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn(
            $this->createPaymentMethod('stripe', new ArrayCollection()),
        );
        $paymentMock->expects($this->never())->method('getDetails');

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($this->createOrderWithPayment($paymentMock));
    }

    public function testDoesNothingWhenPaymentHasNoDetails(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn(
            $this->createPaymentMethod(MollieGatewayFactory::FACTORY_NAME, new ArrayCollection()),
        );
        $paymentMock->method('getDetails')->willReturn([]);

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($this->createOrderWithPayment($paymentMock));
    }

    public function testDoesNothingWhenGatewayHasNoMollieConfiguredMethods(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn(
            $this->createPaymentMethod(MollieGatewayFactory::FACTORY_NAME, null),
        );
        $paymentMock->method('getDetails')->willReturn(['molliePaymentMethods' => 'ideal']);

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($this->createOrderWithPayment($paymentMock));
    }

    public function testDoesNothingWhenNoneOfTheConfiguredMethodsMatches(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn(
            $this->createPaymentMethod(
                MollieGatewayFactory::FACTORY_NAME,
                new ArrayCollection([$this->createMollieGatewayConfig('creditcard')]),
            ),
        );
        $paymentMock->method('getDetails')->willReturn(['molliePaymentMethods' => 'ideal']);

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($this->createOrderWithPayment($paymentMock));
    }

    public function testCalculatesSurchargeForTheSelectedMollieMethod(): void
    {
        $idealConfig = $this->createMollieGatewayConfig('ideal');

        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn(
            $this->createPaymentMethod(
                MollieGatewayFactory::FACTORY_NAME,
                new ArrayCollection([$this->createMollieGatewayConfig('creditcard'), $idealConfig]),
            ),
        );
        $paymentMock->method('getDetails')->willReturn(['molliePaymentMethods' => 'ideal']);

        $orderMock = $this->createOrderWithPayment($paymentMock);

        $this->calculatorMock->expects($this->once())->method('calculate')->with($orderMock, $idealConfig);

        $this->processor->process($orderMock);
    }

    public function testCalculatesSurchargeForTheLastConfiguredMethodWhenDetailsDoNotNameOne(): void
    {
        $creditCardConfig = $this->createMollieGatewayConfig('creditcard');

        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn(
            $this->createPaymentMethod(
                MollieSubscriptionGatewayFactory::FACTORY_NAME,
                new ArrayCollection([$this->createMollieGatewayConfig('ideal'), $creditCardConfig]),
            ),
        );
        $paymentMock->method('getDetails')->willReturn(['cartToken' => 'token']);

        $orderMock = $this->createOrderWithPayment($paymentMock);

        $this->calculatorMock->expects($this->once())->method('calculate')->with($orderMock, $creditCardConfig);

        $this->processor->process($orderMock);
    }

    public function testDoesNothingWhenDetailsDoNotNameAMethodAndNoneAreConfigured(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $paymentMock->method('getMethod')->willReturn(
            $this->createPaymentMethod(MollieGatewayFactory::FACTORY_NAME, new ArrayCollection()),
        );
        $paymentMock->method('getDetails')->willReturn(['cartToken' => 'token']);

        $this->calculatorMock->expects($this->never())->method('calculate');

        $this->processor->process($this->createOrderWithPayment($paymentMock));
    }

    private function createOrderWithPayment(PaymentInterface $payment): OrderInterface
    {
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->method('canBeProcessed')->willReturn(true);
        $orderMock->method('getLastPayment')->willReturn($payment);

        return $orderMock;
    }

    private function createPaymentMethod(string $factoryName, ?ArrayCollection $mollieGatewayConfig): PaymentMethodInterface
    {
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfigMock->method('getFactoryName')->willReturn($factoryName);
        $gatewayConfigMock->method('getMollieGatewayConfig')->willReturn($mollieGatewayConfig);

        $paymentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $paymentMethodMock->method('getGatewayConfig')->willReturn($gatewayConfigMock);

        return $paymentMethodMock;
    }

    private function createMollieGatewayConfig(string $methodId): MollieGatewayConfig
    {
        $config = new MollieGatewayConfig();
        $config->setMethodId($methodId);

        return $config;
    }
}
