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

namespace Tests\Sylius\MolliePlugin\Unit\Guard;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Refund\Guard\PaymentRefundGuard;

final class PaymentRefundGuardTest extends TestCase
{
    private MockObject&PaymentInterface $payment;

    private PaymentRefundGuard $paymentRefundGuard;

    protected function setUp(): void
    {
        $this->payment = $this->createMock(PaymentInterface::class);
        $this->paymentRefundGuard = new PaymentRefundGuard([]);
    }

    public function testPaymentHasNoMethod(): void
    {
        $this->payment->method('getMethod')->willReturn(null);

        self::assertFalse($this->paymentRefundGuard->isRefundPossible($this->payment));
    }

    public function testPaymentMethodHasNoGatewayConfig(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn(null);

        $this->payment->method('getMethod')->willReturn($paymentMethod);

        self::assertFalse($this->paymentRefundGuard->isRefundPossible($this->payment));
    }

    /** @dataProvider getNonMollieGatewayFactories */
    public function testPaymentMethodGatewayFactoryIsNotMollieBased(?string $factoryName): void
    {
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn($factoryName);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);

        $this->payment->method('getMethod')->willReturn($paymentMethod);

        self::assertTrue($this->paymentRefundGuard->isRefundPossible($this->payment));
    }

    /** @dataProvider getMollieBasedRefundAvailability */
    public function testMollieBasedPaymentRefund(string $factory, array $loadedBundles, bool $result): void
    {
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn($factory);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);

        $this->payment->method('getMethod')->willReturn($paymentMethod);

        $this->paymentRefundGuard = new PaymentRefundGuard($loadedBundles);

        self::assertSame($result, $this->paymentRefundGuard->isRefundPossible($this->payment));
    }

    public function getMollieBasedRefundAvailability(): iterable
    {
        yield 'mollie no refund' => [
            'factory' => MollieGatewayFactory::FACTORY_NAME,
            'loadedBundles' => [],
            'result' => false,
        ];
        yield 'mollie refund' => [
            'factory' => MollieGatewayFactory::FACTORY_NAME,
            'loadedBundles' => ['SyliusRefundPlugin' => '\SyliusRefundPlugin\Sylius\RefundPlugin\SyliusRefundPlugin'],
            'result' => true,
        ];
    }

    /** @return iterable<string, array<array-key, string|null>> */
    public function getNonMollieGatewayFactories(): iterable
    {
        yield 'none' => [null];
        yield 'offline' => ['offline'];
    }

    /** @return iterable<string, array<mixed>> */
    public function getLoadedBundles(): iterable
    {
        yield 'no refund plugin' => [[
            'SyliusCoreBundle' => '\Sylius\Bundle\CoreBundle\SyliusCoreBundle',
        ]];
        yield 'refund plugin loaded' => [[
            'SyliusCoreBundle' => '\Sylius\Bundle\CoreBundle\SyliusCoreBundle',
            'SyliusRefundPlugin' => '\SyliusRefundPlugin\Sylius\RefundPlugin\SyliusRefundPlugin',
        ]];
    }
}
