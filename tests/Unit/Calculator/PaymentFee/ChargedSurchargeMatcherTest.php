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

namespace Tests\Sylius\MolliePlugin\Unit\Calculator\PaymentFee;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\ChargedSurchargeMatcher;
use Sylius\MolliePlugin\Calculator\PaymentFee\ChargedSurchargeMatcherInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\PaymentSurchargeAmountCalculatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface as MollieOrderInterface;
use Sylius\MolliePlugin\Exceptions\UnknownPaymentSurchargeType;
use Sylius\MolliePlugin\Model\AdjustmentInterface as MollieAdjustmentInterface;
use Sylius\MolliePlugin\Provider\PaymentSurchargeAdjustmentsProviderInterface;
use Sylius\MolliePlugin\Repository\MollieGatewayConfigRepositoryInterface;

final class ChargedSurchargeMatcherTest extends TestCase
{
    private PaymentSurchargeAdjustmentsProviderInterface $surchargeAdjustmentsProviderMock;

    private PaymentSurchargeAmountCalculatorInterface $surchargeAmountCalculatorMock;

    private MollieGatewayConfigRepositoryInterface $mollieGatewayConfigRepositoryMock;

    private ChargedSurchargeMatcher $matcher;

    protected function setUp(): void
    {
        $this->surchargeAdjustmentsProviderMock = $this->createMock(PaymentSurchargeAdjustmentsProviderInterface::class);
        $this->surchargeAdjustmentsProviderMock->method('getTypes')->willReturn([
            MollieAdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT,
            MollieAdjustmentInterface::PERCENTAGE_ADJUSTMENT,
        ]);
        $this->surchargeAmountCalculatorMock = $this->createMock(PaymentSurchargeAmountCalculatorInterface::class);
        $this->mollieGatewayConfigRepositoryMock = $this->createMock(MollieGatewayConfigRepositoryInterface::class);

        $this->matcher = new ChargedSurchargeMatcher(
            $this->surchargeAdjustmentsProviderMock,
            $this->surchargeAmountCalculatorMock,
            $this->mollieGatewayConfigRepositoryMock,
        );
    }

    public function testImplementsChargedSurchargeMatcherInterface(): void
    {
        $this->assertInstanceOf(ChargedSurchargeMatcherInterface::class, $this->matcher);
    }

    public function testItSumsEverySurchargeAdjustmentTypeOnTheOrder(): void
    {
        $order = $this->orderChargedWith(['fixed_fee' => 500, 'percentage' => 125]);

        $this->assertSame(625, $this->matcher->chargedSurcharge($order));
    }

    public function testItReadsNoSurchargeFromAnOrderWithoutSurchargeAdjustments(): void
    {
        $order = $this->orderChargedWith([]);

        $this->assertSame(0, $this->matcher->chargedSurcharge($order));
    }

    public function testItMatchesAMethodReproducingTheChargedSurcharge(): void
    {
        $order = $this->orderChargedWith(['fixed_fee' => 500]);
        $config = new MollieGatewayConfig();

        $this->surchargeAmountCalculatorMock->method('calculateAmount')->with($order, $config)->willReturn(500);

        $this->assertTrue($this->matcher->matches($order, $config));
    }

    public function testItRejectsAMethodProducingADifferentSurcharge(): void
    {
        $order = $this->orderChargedWith(['fixed_fee' => 500]);
        $config = new MollieGatewayConfig();

        $this->surchargeAmountCalculatorMock->method('calculateAmount')->with($order, $config)->willReturn(400);

        $this->assertFalse($this->matcher->matches($order, $config));
    }

    public function testItMatchesAMethodOfAModelReplacingTheBundledEntity(): void
    {
        $order = $this->orderChargedWith(['fixed_fee' => 500]);
        $config = $this->createMock(MollieGatewayConfigInterface::class);

        $this->surchargeAmountCalculatorMock->method('calculateAmount')->with($order, $config)->willReturn(500);

        $this->assertTrue($this->matcher->matches($order, $config));
    }

    public function testItFindsAGatewayKeepingTheTotalWhenOneOfItsMethodsMatches(): void
    {
        $order = $this->orderChargedWith(['fixed_fee' => 500]);
        $satispay = new MollieGatewayConfig();
        $ideal = new MollieGatewayConfig();

        $this->expectEnabledConfigs([$satispay, $ideal]);
        $this->surchargeAmountCalculatorMock->method('calculateAmount')->willReturnCallback(
            fn ($ignored, MollieGatewayConfig $config): int => $config === $ideal ? 500 : 400,
        );

        $this->assertTrue($this->matcher->gatewayKeepsTheTotal($order, $this->createMock(GatewayConfigInterface::class)));
    }

    public function testItFindsNoGatewayKeepingTheTotalWhenEveryMethodProducesADifferentSurcharge(): void
    {
        $order = $this->orderChargedWith(['fixed_fee' => 500]);

        $this->expectEnabledConfigs([new MollieGatewayConfig(), new MollieGatewayConfig()]);
        $this->surchargeAmountCalculatorMock->method('calculateAmount')->willReturn(400);

        $this->assertFalse($this->matcher->gatewayKeepsTheTotal($order, $this->createMock(GatewayConfigInterface::class)));
    }

    public function testItFindsNoGatewayKeepingTheTotalWhenItHasNoEnabledMethod(): void
    {
        $order = $this->orderChargedWith(['fixed_fee' => 500]);

        $this->expectEnabledConfigs([]);
        $this->surchargeAmountCalculatorMock->expects($this->never())->method('calculateAmount');

        $this->assertFalse($this->matcher->gatewayKeepsTheTotal($order, $this->createMock(GatewayConfigInterface::class)));
    }

    public function testItPropagatesAnUnknownSurchargeType(): void
    {
        $order = $this->orderChargedWith(['fixed_fee' => 500]);

        $this->expectEnabledConfigs([new MollieGatewayConfig()]);
        $this->surchargeAmountCalculatorMock->method('calculateAmount')->willThrowException(
            new UnknownPaymentSurchargeType('no calculator supports payment type: custom'),
        );

        $this->expectException(UnknownPaymentSurchargeType::class);

        $this->matcher->gatewayKeepsTheTotal($order, $this->createMock(GatewayConfigInterface::class));
    }

    /**
     * `findAllEnabledByGateway()` selects the amount limits alongside the entity, so Doctrine hands
     * back rows rather than the entities its return type advertises.
     *
     * @param MollieGatewayConfig[] $configs
     */
    private function expectEnabledConfigs(array $configs): void
    {
        $this->mollieGatewayConfigRepositoryMock->method('findAllEnabledByGateway')->willReturn(array_map(
            fn (MollieGatewayConfig $config): array => [0 => $config, 'minimumAmount' => null, 'maximumAmount' => null],
            $configs,
        ));
    }

    /** @param array<string, int> $surchargePerType */
    private function orderChargedWith(array $surchargePerType): MollieOrderInterface
    {
        $order = $this->createMock(MollieOrderInterface::class);
        $order->method('getAdjustments')->willReturnCallback(
            function (?string $type = null) use ($surchargePerType): Collection {
                if (null === $type || !isset($surchargePerType[$type])) {
                    return new ArrayCollection();
                }

                $adjustment = $this->createMock(AdjustmentInterface::class);
                $adjustment->method('getAmount')->willReturn($surchargePerType[$type]);

                return new ArrayCollection([$adjustment]);
            },
        );

        return $order;
    }
}
