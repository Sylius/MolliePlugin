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

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdjustmentInterface as CoreAdjustmentInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\CompositePaymentSurchargeCalculator;
use Sylius\MolliePlugin\Calculator\PaymentFee\FixedAmountAndPercentageCalculator;
use Sylius\MolliePlugin\Calculator\PaymentFee\FixedAmountCalculator;
use Sylius\MolliePlugin\Calculator\PaymentFee\NoFeeCalculator;
use Sylius\MolliePlugin\Calculator\PaymentFee\PaymentSurchargeCalculatorInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\PercentageCalculator;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Entity\PaymentSurchargeFee;
use Sylius\MolliePlugin\Exceptions\UnknownPaymentSurchargeType;
use Sylius\MolliePlugin\Model\AdjustmentInterface;
use Sylius\MolliePlugin\Model\PaymentSurchargeFeeType;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;

final class PaymentSurchargeAmountCalculatorTest extends TestCase
{
    private DivisorProviderInterface $divisorProviderMock;

    private AdjustmentFactoryInterface $adjustmentFactoryMock;

    protected function setUp(): void
    {
        $this->divisorProviderMock = $this->createMock(DivisorProviderInterface::class);
        $this->divisorProviderMock->method('getDivisor')->willReturn(100);

        $this->adjustmentFactoryMock = $this->createMock(AdjustmentFactoryInterface::class);
        $this->adjustmentFactoryMock->method('createNew')->willReturnCallback(
            fn (): CoreAdjustmentInterface => $this->createMock(CoreAdjustmentInterface::class),
        );
    }

    public function testNoFeeReportsNothing(): void
    {
        $calculator = new NoFeeCalculator();

        $this->assertSame(0, $calculator->calculateAmount(
            $this->order(10000),
            $this->config(PaymentSurchargeFeeType::NONE),
        ));
    }

    public function testFixedAmountReportsTheConfiguredAmountInMinorUnits(): void
    {
        $calculator = new FixedAmountCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock);
        $config = $this->config(PaymentSurchargeFeeType::FIXED, fixedAmount: 4.0);

        $this->assertSame(400, $calculator->calculateAmount($this->order(10000), $config));
    }

    public function testFixedAmountIgnoresTheSurchargeLimit(): void
    {
        $calculator = new FixedAmountCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock);
        $config = $this->config(PaymentSurchargeFeeType::FIXED, fixedAmount: 4.0, surchargeLimit: 1.0);

        $this->assertSame(400, $calculator->calculateAmount($this->order(10000), $config));
    }

    public function testPercentageIsTakenFromTheItemsTotalAndRoundedUp(): void
    {
        $calculator = new PercentageCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock);
        $config = $this->config(PaymentSurchargeFeeType::PERCENTAGE, percentage: 3.0, surchargeLimit: 0.0);

        // 3% of 100.01 is 300.03 minor units.
        $this->assertSame(301, $calculator->calculateAmount($this->order(10001), $config));
    }

    public function testPercentageIsCappedByTheSurchargeLimit(): void
    {
        $calculator = new PercentageCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock);
        $config = $this->config(PaymentSurchargeFeeType::PERCENTAGE, percentage: 10.0, surchargeLimit: 5.0);

        $this->assertSame(500, $calculator->calculateAmount($this->order(20000), $config));
    }

    public function testPercentageIsNotCappedWhenTheLimitIsZero(): void
    {
        $calculator = new PercentageCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock);
        $config = $this->config(PaymentSurchargeFeeType::PERCENTAGE, percentage: 10.0, surchargeLimit: 0.0);

        $this->assertSame(2000, $calculator->calculateAmount($this->order(20000), $config));
    }

    public function testFixedAndPercentageAddsUpPartsThatAreEachAlreadyRounded(): void
    {
        $calculator = $this->fixedAndPercentageCalculator();
        // 3% of 100.01 rounds up to 301 on its own before the fixed 400 is added.
        $config = $this->config(
            PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE,
            fixedAmount: 4.0,
            percentage: 3.0,
            surchargeLimit: 100.0,
        );

        $this->assertSame(701, $calculator->calculateAmount($this->order(10001), $config));
    }

    public function testFixedAndPercentageIsCappedByTheSurchargeLimit(): void
    {
        $calculator = $this->fixedAndPercentageCalculator();
        $config = $this->config(
            PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE,
            fixedAmount: 4.0,
            percentage: 10.0,
            surchargeLimit: 6.0,
        );

        $this->assertSame(600, $calculator->calculateAmount($this->order(20000), $config));
    }

    public function testFixedAndPercentageAddsASingleAdjustmentForTheWholeSurcharge(): void
    {
        $config = $this->config(
            PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE,
            fixedAmount: 4.0,
            percentage: 3.0,
            surchargeLimit: 100.0,
        );

        $adjustment = $this->createMock(CoreAdjustmentInterface::class);
        $adjustment->expects($this->once())->method('setType')->with(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT);
        $adjustment->expects($this->once())->method('setAmount')->with(700);

        $adjustmentFactory = $this->createMock(AdjustmentFactoryInterface::class);
        $adjustmentFactory->expects($this->once())->method('createNew')->willReturn($adjustment);

        $order = $this->order(10000);
        $order->expects($this->once())->method('addAdjustment')->with($adjustment);
        $order->expects($this->never())->method('removeAdjustment');

        $calculator = new FixedAmountAndPercentageCalculator(
            $adjustmentFactory,
            new PercentageCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock),
            new FixedAmountCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock),
            $this->divisorProviderMock,
        );

        $calculator->calculate($order, $config);
    }

    public function testCompositeReportsTheAmountOfTheSupportingCalculator(): void
    {
        $composite = new CompositePaymentSurchargeCalculator([
            new NoFeeCalculator(),
            new FixedAmountCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock),
        ]);

        $this->assertSame(400, $composite->calculateAmount(
            $this->order(10000),
            $this->config(PaymentSurchargeFeeType::FIXED, fixedAmount: 4.0),
        ));
    }

    public function testCompositeFallsBackToNoFeeWhenTheMethodHasNoSurchargeConfigured(): void
    {
        $composite = new CompositePaymentSurchargeCalculator([new NoFeeCalculator()]);

        $config = $this->createMock(MollieGatewayConfig::class);
        $config->method('getPaymentSurchargeFee')->willReturn(null);

        $this->assertSame(0, $composite->calculateAmount($this->order(10000), $config));
    }

    public function testCompositeRefusesToReportAnAmountForACalculatorThatOnlyApplies(): void
    {
        $applyOnlyCalculator = new class() implements PaymentSurchargeCalculatorInterface {
            public function supports(string $type): bool
            {
                return true;
            }

            public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
            {
            }
        };

        $composite = new CompositePaymentSurchargeCalculator([$applyOnlyCalculator]);

        $this->expectException(UnknownPaymentSurchargeType::class);

        $composite->calculateAmount($this->order(10000), $this->config(PaymentSurchargeFeeType::FIXED, fixedAmount: 4.0));
    }

    public function testCompositeFailsWhenNoCalculatorSupportsTheType(): void
    {
        $composite = new CompositePaymentSurchargeCalculator([new NoFeeCalculator()]);

        $this->expectException(UnknownPaymentSurchargeType::class);

        $composite->calculateAmount($this->order(10000), $this->config(PaymentSurchargeFeeType::FIXED, fixedAmount: 4.0));
    }

    private function fixedAndPercentageCalculator(): FixedAmountAndPercentageCalculator
    {
        return new FixedAmountAndPercentageCalculator(
            $this->adjustmentFactoryMock,
            new PercentageCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock),
            new FixedAmountCalculator($this->adjustmentFactoryMock, $this->divisorProviderMock),
            $this->divisorProviderMock,
        );
    }

    private function order(int $itemsTotal): OrderInterface
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getItemsTotal')->willReturn($itemsTotal);

        return $order;
    }

    private function config(
        string $type,
        ?float $fixedAmount = null,
        ?float $percentage = null,
        ?float $surchargeLimit = null,
    ): MollieGatewayConfig {
        $fee = new PaymentSurchargeFee();
        $fee->setType($type);
        $fee->setFixedAmount($fixedAmount);
        $fee->setPercentage($percentage);
        $fee->setSurchargeLimit($surchargeLimit);

        $config = $this->createMock(MollieGatewayConfig::class);
        $config->method('getPaymentSurchargeFee')->willReturn($fee);

        return $config;
    }
}
