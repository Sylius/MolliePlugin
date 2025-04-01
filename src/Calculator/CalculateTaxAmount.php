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

namespace Sylius\MolliePlugin\Calculator;

use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;

final class CalculateTaxAmount implements CalculateTaxAmountInterface
{
    public function __construct(private readonly IntToStringConverterInterface $converter)
    {
    }

    public function calculate(float $taxRateAmount, int $amount): string
    {
        $shippingTaxAmount = round($amount - ($amount / (1 + $taxRateAmount)));

        return $this->converter->convertIntToString((int) $shippingTaxAmount);
    }
}
