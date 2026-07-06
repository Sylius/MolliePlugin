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

namespace Tests\Sylius\MolliePlugin\Unit\Resolver;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface as SyliusOrderInterface;
use Sylius\MolliePlugin\Entity\OrderInterface as MollieOrderInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Repository\MollieGatewayConfigRepository;
use Sylius\MolliePlugin\Repository\Query\MollieBasedPaymentMethodQueryInterface;
use Sylius\MolliePlugin\Resolver\MollieAllowedMethodsResolverInterface;
use Sylius\MolliePlugin\Resolver\MollieCountriesRestrictionResolverInterface;
use Sylius\MolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Sylius\MolliePlugin\Resolver\MolliePaymentsMethodResolver;
use Sylius\MolliePlugin\Resolver\MolliePaymentsMethodResolverInterface;
use Sylius\MolliePlugin\Resolver\Order\PaymentCheckoutOrderResolverInterface;
use Sylius\MolliePlugin\Voucher\Checker\ProductVoucherTypeCheckerInterface;

final class MolliePaymentsMethodResolverTest extends TestCase
{
    private MollieGatewayConfigRepository $mollieGatewayRepositoryMock;

    private MollieCountriesRestrictionResolverInterface $countriesRestrictionResolverMock;

    private ProductVoucherTypeCheckerInterface $productVoucherTypeCheckerMock;

    private PaymentCheckoutOrderResolverInterface $paymentCheckoutOrderResolverMock;

    private MollieBasedPaymentMethodQueryInterface $mollieBasedPaymentMethodQueryMock;

    private MollieAllowedMethodsResolverInterface $allowedMethodsResolverMock;

    private MollieLoggerActionInterface $loggerActionMock;

    private MollieFactoryNameResolverInterface $mollieFactoryNameResolverMock;

    private DivisorProviderInterface $divisorProviderMock;

    private MolliePaymentsMethodResolver $resolver;

    protected function setUp(): void
    {
        $this->mollieGatewayRepositoryMock = $this->createMock(MollieGatewayConfigRepository::class);
        $this->countriesRestrictionResolverMock = $this->createMock(MollieCountriesRestrictionResolverInterface::class);
        $this->productVoucherTypeCheckerMock = $this->createMock(ProductVoucherTypeCheckerInterface::class);
        $this->paymentCheckoutOrderResolverMock = $this->createMock(PaymentCheckoutOrderResolverInterface::class);
        $this->mollieBasedPaymentMethodQueryMock = $this->createMock(MollieBasedPaymentMethodQueryInterface::class);
        $this->allowedMethodsResolverMock = $this->createMock(MollieAllowedMethodsResolverInterface::class);
        $this->loggerActionMock = $this->createMock(MollieLoggerActionInterface::class);
        $this->mollieFactoryNameResolverMock = $this->createMock(MollieFactoryNameResolverInterface::class);
        $this->divisorProviderMock = $this->createMock(DivisorProviderInterface::class);

        $this->resolver = new MolliePaymentsMethodResolver(
            $this->mollieGatewayRepositoryMock,
            $this->countriesRestrictionResolverMock,
            $this->productVoucherTypeCheckerMock,
            $this->paymentCheckoutOrderResolverMock,
            $this->mollieBasedPaymentMethodQueryMock,
            $this->allowedMethodsResolverMock,
            $this->loggerActionMock,
            $this->mollieFactoryNameResolverMock,
            $this->divisorProviderMock,
        );
    }

    public function testImplementsMolliePaymentsMethodResolverInterface(): void
    {
        $this->assertInstanceOf(MolliePaymentsMethodResolverInterface::class, $this->resolver);
    }

    public function testReturnsDefaultOptionsWhenOrderHasNoAddress(): void
    {
        $orderMock = $this->createMock(MollieOrderInterface::class);
        $orderMock->method('getBillingAddress')->willReturn(null);
        $orderMock->method('getShippingAddress')->willReturn(null);

        $this->paymentCheckoutOrderResolverMock->method('resolve')->willReturn($orderMock);
        $this->mollieFactoryNameResolverMock->expects($this->never())->method('resolve');

        $this->assertSame($this->defaultOptions(), $this->resolver->resolve());
    }

    public function testReturnsDefaultOptionsWhenOrderIsNotMollieOrder(): void
    {
        $addressMock = $this->createMock(AddressInterface::class);

        $orderMock = $this->createMock(SyliusOrderInterface::class);
        $orderMock->method('getBillingAddress')->willReturn($addressMock);

        $this->paymentCheckoutOrderResolverMock->method('resolve')->willReturn($orderMock);
        $this->mollieFactoryNameResolverMock->expects($this->never())->method('resolve');

        $this->assertSame($this->defaultOptions(), $this->resolver->resolve());
    }

    public function testReturnsDefaultOptionsWhenCountryCodeIsNull(): void
    {
        $addressMock = $this->createMock(AddressInterface::class);
        $addressMock->method('getCountryCode')->willReturn(null);

        $orderMock = $this->createMock(MollieOrderInterface::class);
        $orderMock->method('getBillingAddress')->willReturn($addressMock);

        $this->paymentCheckoutOrderResolverMock->method('resolve')->willReturn($orderMock);
        $this->mollieFactoryNameResolverMock->expects($this->never())->method('resolve');

        $this->assertSame($this->defaultOptions(), $this->resolver->resolve());
    }

    public function testResolvesPaymentOptionsWhenCountryCodeIsPresent(): void
    {
        $addressMock = $this->createMock(AddressInterface::class);
        $addressMock->method('getCountryCode')->willReturn('NL');

        $orderMock = $this->createMock(MollieOrderInterface::class);
        $orderMock->method('getBillingAddress')->willReturn($addressMock);
        $orderMock->method('getChannel')->willReturn($this->createMock(ChannelInterface::class));

        $this->paymentCheckoutOrderResolverMock->method('resolve')->willReturn($orderMock);
        $this->mollieFactoryNameResolverMock->expects($this->once())->method('resolve')->with($orderMock)->willReturn('mollie');
        $this->mollieBasedPaymentMethodQueryMock->method('getOneByChannelAndFactoryName')->willReturn(null);

        $this->assertSame($this->defaultOptions(), $this->resolver->resolve());
    }

    /**
     * @return array{
     *     data: array<string, string>,
     *     image: array<string, string>,
     *     issuers: array<string, mixed>|null,
     *     paymentFee: array<string, mixed>
     * }
     */
    private function defaultOptions(): array
    {
        return [
            'data' => [],
            'image' => [],
            'issuers' => [],
            'paymentFee' => [],
        ];
    }
}
