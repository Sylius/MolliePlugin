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

namespace Tests\Sylius\MolliePlugin\Unit\Payments\MethodResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Filter\MollieMethodFilter;
use Sylius\MolliePlugin\Filter\MollieMethodFilterInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;

final class MollieMethodFilterTest extends TestCase
{
    private MollieMethodFilterInterface $mollieMethodFilter;

    protected function setUp(): void
    {
        $this->mollieMethodFilter = new MollieMethodFilter();
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(MollieMethodFilterInterface::class, $this->mollieMethodFilter);
    }

    public function testReturnsNonRecurringMethod(): void
    {
        $method1Mock = $this->createMock(PaymentMethodInterface::class);
        $method2Mock = $this->createMock(PaymentMethodInterface::class);
        $config1Mock = $this->createMock(GatewayConfigInterface::class);
        $config2Mock = $this->createMock(GatewayConfigInterface::class);

        $paymentMethods = [$method1Mock, $method2Mock];

        $method1Mock->expects($this->exactly(2))
        ->method('getGatewayConfig')
            ->willReturn($config1Mock)
        ;
        $config1Mock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME)
        ;

        $method2Mock->expects($this->exactly(2))
        ->method('getGatewayConfig')
            ->willReturn($config2Mock)
        ;
        $config2Mock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn('not_mollie_subscription')
        ;

        $filteredMethods = $this->mollieMethodFilter->nonRecurringFilter($paymentMethods);

        $this->assertSame([$method2Mock], $filteredMethods);
    }

    public function testReturnsRecurringMethod(): void
    {
        $method1Mock = $this->createMock(PaymentMethodInterface::class);
        $method2Mock = $this->createMock(PaymentMethodInterface::class);
        $config1Mock = $this->createMock(GatewayConfigInterface::class);
        $config2Mock = $this->createMock(GatewayConfigInterface::class);

        $paymentMethods = [$method1Mock, $method2Mock];

        $method1Mock->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($config1Mock)
        ;
        $config1Mock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME)
        ;

        $method2Mock->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($config2Mock)
        ;
        $config2Mock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME)
        ;

        $filteredMethods = $this->mollieMethodFilter->recurringFilter($paymentMethods);

        $this->assertSame([$method2Mock], $filteredMethods);
    }

    #[DataProvider('nonSubscriptionFactoryNameProvider')]
    public function testRemovesNonSubscriptionMethodsFromRecurringMethods(string $factoryName): void
    {
        $methodMock = $this->createMock(PaymentMethodInterface::class);
        $configMock = $this->createMock(GatewayConfigInterface::class);

        $methodMock->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($configMock)
        ;
        $configMock->expects($this->once())
            ->method('getFactoryName')
            ->willReturn($factoryName)
        ;

        $this->assertSame([], $this->mollieMethodFilter->recurringFilter([$methodMock]));
    }

    public function testRemovesMethodsWithoutGatewayConfigFromRecurringMethods(): void
    {
        $methodMock = $this->createMock(PaymentMethodInterface::class);

        $methodMock->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn(null)
        ;

        $this->assertSame([], $this->mollieMethodFilter->recurringFilter([$methodMock]));
    }

    /** @return iterable<string, array{string}> */
    public static function nonSubscriptionFactoryNameProvider(): iterable
    {
        yield 'mollie' => [MollieGatewayFactory::FACTORY_NAME];
        yield 'offline' => ['offline'];
        yield 'paypal' => ['paypal'];
        yield 'stripe' => ['stripe_checkout'];
    }
}
