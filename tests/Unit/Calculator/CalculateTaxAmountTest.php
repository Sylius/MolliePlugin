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

namespace Tests\Sylius\MolliePlugin\Unit\Calculator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Calculator\CalculateTaxAmount;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;

final class CalculateTaxAmountTest extends TestCase
{
    private MockObject $converterMock;

    private CalculateTaxAmount $calculateTaxAmount;

    protected function setUp(): void
    {
        $this->converterMock = $this->createMock(IntToStringConverterInterface::class);
        $this->calculateTaxAmount = new CalculateTaxAmount($this->converterMock);
    }

    public function testCalculates(): void
    {
        $taxRateAmount = 0.2;
        $amount = 1000;
        $expectedShippingTaxAmount = round($amount - ($amount / (1 + $taxRateAmount)));

        $this->converterMock
            ->expects($this->once())
            ->method('convertIntToString')
            ->with((int) $expectedShippingTaxAmount)
            ->willReturn('200');

        $result = $this->calculateTaxAmount->calculate($taxRateAmount, $amount);
        $this->assertSame('200', $result);
    }
}
