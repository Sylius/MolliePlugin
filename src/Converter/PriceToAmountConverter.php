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

namespace Sylius\MolliePlugin\Converter;

use Sylius\Bundle\MoneyBundle\Templating\Helper\FormatMoneyHelper;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

final class PriceToAmountConverter implements PriceToAmountConverterInterface
{
    public function __construct(
        private readonly CurrencyContextInterface $currencyContext,
        private readonly LocaleContextInterface $localeContext,
        private readonly FormatMoneyHelper $formatMoneyHelper,
    ) {
    }

    public function convert(int $price): string
    {
        return $this->formatMoneyHelper->formatAmount(
            $price,
            $this->currencyContext->getCurrencyCode(),
            $this->localeContext->getLocaleCode(),
        );
    }
}
