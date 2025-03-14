<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider\Divisor;

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
