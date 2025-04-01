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

namespace Sylius\MolliePlugin\Provider;

use Payum\Core\Request\GetCurrency;

final class DivisorProvider implements DivisorProviderInterface
{
    private const DIVISOR = 100;

    public function getDivisor(): int
    {
        return self::DIVISOR;
    }

    public function getDivisorForCurrency(GetCurrency $currency): int
    {
        return 10 ** $currency->exp;
    }
}
