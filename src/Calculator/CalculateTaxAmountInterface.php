<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Calculator;

interface CalculateTaxAmountInterface
{
    public function calculate(float $taxRateAmount, int $shippingAmount): string;
}
