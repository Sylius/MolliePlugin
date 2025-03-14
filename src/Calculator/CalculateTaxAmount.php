<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Calculator;

use Sylius\MolliePlugin\Helper\IntToStringConverterInterface;

final class CalculateTaxAmount implements CalculateTaxAmountInterface
{
    /** @var IntToStringConverterInterface */
    private $converter;

    public function __construct(IntToStringConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function calculate(float $taxRateAmount, int $amount): string
    {
        $shippingTaxAmount = round($amount - ($amount / (1 + $taxRateAmount)));

        return $this->converter->convertIntToString((int) $shippingTaxAmount);
    }
}
