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

namespace Tests\Sylius\MolliePlugin\Unit\ApplePay\Checker;

use Mollie\Api\Types\PaymentMethod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledChecker;
use Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledCheckerInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

final class ApplePayEnabledCheckerTest extends TestCase
{
    private MockObject|RepositoryInterface $mollieGatewayConfigurationRepository;

    private MockObject|PaymentMethodsResolverInterface $paymentMethodsResolver;

    private ApplePayEnabledCheckerInterface $checker;

    protected function setUp(): void
    {
        $this->mollieGatewayConfigurationRepository = $this->createMock(RepositoryInterface::class);
        $this->paymentMethodsResolver = $this->createMock(PaymentMethodsResolverInterface::class);

        $this->checker = new ApplePayEnabledChecker(
            $this->mollieGatewayConfigurationRepository,
            $this->paymentMethodsResolver,
        );
    }

    public function testIsEnabledReturnsTrueWhenApplePayIsEnabledAndDirectButtonIsEnabled(): void
    {
        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(true);

        $this->mollieGatewayConfigurationRepository->expects($this->once())
            ->method('findBy')
            ->with(['methodId' => PaymentMethod::APPLEPAY])
            ->willReturn([$mollieGatewayConfig]);

        $this->assertTrue($this->checker->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenApplePayIsDisabled(): void
    {
        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->mollieGatewayConfigurationRepository->expects($this->once())
            ->method('findBy')
            ->with(['methodId' => PaymentMethod::APPLEPAY])
            ->willReturn([$mollieGatewayConfig]);

        $this->assertFalse($this->checker->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenApplePayDirectButtonIsDisabled(): void
    {
        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(false);

        $this->mollieGatewayConfigurationRepository->expects($this->once())
            ->method('findBy')
            ->with(['methodId' => PaymentMethod::APPLEPAY])
            ->willReturn([$mollieGatewayConfig]);

        $this->assertFalse($this->checker->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenApplePayDirectButtonIsNull(): void
    {
        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(null);

        $this->mollieGatewayConfigurationRepository->expects($this->once())
            ->method('findBy')
            ->with(['methodId' => PaymentMethod::APPLEPAY])
            ->willReturn([$mollieGatewayConfig]);

        $this->assertFalse($this->checker->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenNoApplePayConfigFound(): void
    {
        $this->mollieGatewayConfigurationRepository->expects($this->once())
            ->method('findBy')
            ->with(['methodId' => PaymentMethod::APPLEPAY])
            ->willReturn([]);

        $this->assertFalse($this->checker->isEnabled());
    }

    public function testIsEnabledForOrderFallsBackToIsEnabledWhenOrderIsNull(): void
    {
        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(true);

        $this->mollieGatewayConfigurationRepository->expects($this->once())
            ->method('findBy')
            ->with(['methodId' => PaymentMethod::APPLEPAY])
            ->willReturn([$mollieGatewayConfig]);

        $this->assertTrue($this->checker->isEnabledForOrder(null));
    }

    public function testIsEnabledForOrderFallsBackToIsEnabledWhenOrderHasNoLastPayment(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn(null);

        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(true);

        $this->mollieGatewayConfigurationRepository->expects($this->once())
            ->method('findBy')
            ->with(['methodId' => PaymentMethod::APPLEPAY])
            ->willReturn([$mollieGatewayConfig]);

        $this->assertTrue($this->checker->isEnabledForOrder($order));
    }

    public function testIsEnabledForOrderFallsBackToIsEnabledWhenPaymentMethodsResolverIsNull(): void
    {
        $checkerWithoutResolver = new ApplePayEnabledChecker(
            $this->mollieGatewayConfigurationRepository,
            null,
        );

        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($payment);

        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(true);

        $this->mollieGatewayConfigurationRepository->expects($this->once())
            ->method('findBy')
            ->with(['methodId' => PaymentMethod::APPLEPAY])
            ->willReturn([$mollieGatewayConfig]);

        $this->assertTrue($checkerWithoutResolver->isEnabledForOrder($order));
    }

    public function testIsEnabledForOrderReturnsTrueWhenApplePayMethodFoundInSupportedMethods(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($payment);

        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(true);

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->expects($this->once())
            ->method('getMethodByName')
            ->with(PaymentMethod::APPLEPAY)
            ->willReturn($mollieGatewayConfig);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->paymentMethodsResolver->expects($this->once())
            ->method('getSupportedMethods')
            ->with($payment)
            ->willReturn([$paymentMethod]);

        $this->assertTrue($this->checker->isEnabledForOrder($order));
    }

    public function testIsEnabledForOrderReturnsFalseWhenApplePayMethodDisabledInSupportedMethods(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($payment);

        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->expects($this->once())
            ->method('getMethodByName')
            ->with(PaymentMethod::APPLEPAY)
            ->willReturn($mollieGatewayConfig);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->paymentMethodsResolver->expects($this->once())
            ->method('getSupportedMethods')
            ->with($payment)
            ->willReturn([$paymentMethod]);

        $this->assertFalse($this->checker->isEnabledForOrder($order));
    }

    public function testIsEnabledForOrderReturnsFalseWhenApplePayDirectButtonDisabledInSupportedMethods(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($payment);

        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(false);

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->expects($this->once())
            ->method('getMethodByName')
            ->with(PaymentMethod::APPLEPAY)
            ->willReturn($mollieGatewayConfig);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->paymentMethodsResolver->expects($this->once())
            ->method('getSupportedMethods')
            ->with($payment)
            ->willReturn([$paymentMethod]);

        $this->assertFalse($this->checker->isEnabledForOrder($order));
    }

    public function testIsEnabledForOrderReturnsFalseWhenNoApplePayMethodInSupportedMethods(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($payment);

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->expects($this->once())
            ->method('getMethodByName')
            ->with(PaymentMethod::APPLEPAY)
            ->willReturn(null);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->paymentMethodsResolver->expects($this->once())
            ->method('getSupportedMethods')
            ->with($payment)
            ->willReturn([$paymentMethod]);

        $this->assertFalse($this->checker->isEnabledForOrder($order));
    }

    public function testIsEnabledForOrderReturnsFalseWhenNoSupportedMethods(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($payment);

        $this->paymentMethodsResolver->expects($this->once())
            ->method('getSupportedMethods')
            ->with($payment)
            ->willReturn([]);

        $this->assertFalse($this->checker->isEnabledForOrder($order));
    }

    public function testIsEnabledForOrderSkipsNonGatewayConfigInterfaceMethods(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($payment);

        $nonMollieGatewayConfig = $this->createMock(\Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface::class);

        $paymentMethodWithoutMollieGateway = $this->createMock(PaymentMethodInterface::class);
        $paymentMethodWithoutMollieGateway->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($nonMollieGatewayConfig);

        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(true);

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->expects($this->once())
            ->method('getMethodByName')
            ->with(PaymentMethod::APPLEPAY)
            ->willReturn($mollieGatewayConfig);

        $paymentMethodWithMollieGateway = $this->createMock(PaymentMethodInterface::class);
        $paymentMethodWithMollieGateway->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->paymentMethodsResolver->expects($this->once())
            ->method('getSupportedMethods')
            ->with($payment)
            ->willReturn([$paymentMethodWithoutMollieGateway, $paymentMethodWithMollieGateway]);

        $this->assertTrue($this->checker->isEnabledForOrder($order));
    }

    public function testIsEnabledForOrderSkipsMethodsWithNullGatewayConfig(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order->expects($this->once())
            ->method('getLastPayment')
            ->willReturn($payment);

        $paymentMethodWithNullGateway = $this->createMock(PaymentMethodInterface::class);
        $paymentMethodWithNullGateway->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn(null);

        $mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieGatewayConfig->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $mollieGatewayConfig->expects($this->once())
            ->method('isApplePayDirectButton')
            ->willReturn(true);

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->expects($this->once())
            ->method('getMethodByName')
            ->with(PaymentMethod::APPLEPAY)
            ->willReturn($mollieGatewayConfig);

        $paymentMethodWithMollieGateway = $this->createMock(PaymentMethodInterface::class);
        $paymentMethodWithMollieGateway->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $this->paymentMethodsResolver->expects($this->once())
            ->method('getSupportedMethods')
            ->with($payment)
            ->willReturn([$paymentMethodWithNullGateway, $paymentMethodWithMollieGateway]);

        $this->assertTrue($this->checker->isEnabledForOrder($order));
    }
}
