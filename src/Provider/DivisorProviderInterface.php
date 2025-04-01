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

interface DivisorProviderInterface
{
    public function getDivisor(): int;

    public function getDivisorForCurrency(GetCurrency $currency): int;
}
