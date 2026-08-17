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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\PaymentSurchargeAmountCalculatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
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

    private PaymentSurchargeAmountCalculatorInterface $surchargeAmountCalculatorMock;

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
        $this->surchargeAmountCalculatorMock = $this->createMock(PaymentSurchargeAmountCalculatorInterface::class);

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
            $this->surchargeAmountCalculatorMock,
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

    public function testItKeepsOnlyMethodsMatchingTheSurchargeAlreadyChargedOnAPlacedOrder(): void
    {
        $ideal = $this->config('ideal');
        $satispay = $this->config('satispay');

        $order = $this->orderChargedWith(500, new \DateTimeImmutable());
        $this->expectMethodsOffered($order, [$ideal, $satispay], ['ideal' => 500, 'satispay' => 400]);

        $this->countriesRestrictionResolverMock->expects($this->once())
            ->method('resolve')
            ->with($ideal, $this->anything(), 'NL')
            ->willReturn($this->defaultOptions())
        ;

        $this->resolver->resolve();
    }

    public function testItOffersEverySurchargeDuringCheckout(): void
    {
        $ideal = $this->config('ideal');
        $satispay = $this->config('satispay');

        $order = $this->orderChargedWith(500, null);
        $this->expectMethodsOffered($order, [$ideal, $satispay], ['ideal' => 500, 'satispay' => 400]);

        $this->countriesRestrictionResolverMock->expects($this->exactly(2))
            ->method('resolve')
            ->willReturn($this->defaultOptions())
        ;

        $this->resolver->resolve();
    }

    private function config(string $methodId): MollieGatewayConfig
    {
        $config = $this->createMock(MollieGatewayConfig::class);
        $config->method('getMethodId')->willReturn($methodId);

        return $config;
    }

    private function orderChargedWith(int $surcharge, ?\DateTimeImmutable $checkoutCompletedAt): MollieOrderInterface
    {
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $adjustment->method('getAmount')->willReturn($surcharge);

        $address = $this->createMock(AddressInterface::class);
        $address->method('getCountryCode')->willReturn('NL');

        $order = $this->createMock(MollieOrderInterface::class);
        $order->method('getBillingAddress')->willReturn($address);
        $order->method('getChannel')->willReturn($this->createMock(ChannelInterface::class));
        $order->method('getCheckoutCompletedAt')->willReturn($checkoutCompletedAt);
        $order->method('getTotal')->willReturn(7597);
        $order->method('getAdjustments')->willReturnCallback(
            fn (?string $type = null): Collection => new ArrayCollection(
                'fixed_fee' === $type ? [$adjustment] : [],
            ),
        );

        return $order;
    }

    /**
     * @param MollieGatewayConfig[] $configs
     * @param array<string, int> $surchargePerMethod
     */
    private function expectMethodsOffered(MollieOrderInterface $order, array $configs, array $surchargePerMethod): void
    {
        $this->paymentCheckoutOrderResolverMock->method('resolve')->willReturn($order);
        $this->mollieFactoryNameResolverMock->method('resolve')->willReturn('mollie');

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($this->createMock(GatewayConfigInterface::class));
        $this->mollieBasedPaymentMethodQueryMock->method('getOneByChannelAndFactoryName')->willReturn($paymentMethod);

        $this->mollieGatewayRepositoryMock->method('findAllEnabledByGateway')->willReturn(array_map(
            fn (MollieGatewayConfig $config): array => [0 => $config, 'minimumAmount' => null, 'maximumAmount' => null],
            $configs,
        ));

        $this->allowedMethodsResolverMock->method('resolve')->willReturn(array_keys($surchargePerMethod));
        $this->divisorProviderMock->method('getDivisor')->willReturn(100);

        $this->surchargeAmountCalculatorMock->method('calculateAmount')->willReturnCallback(
            fn ($ignored, MollieGatewayConfig $config): int => $surchargePerMethod[$config->getMethodId()],
        );

        $this->productVoucherTypeCheckerMock->method('checkTheProductTypeOnCart')->willReturnArgument(1);
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
