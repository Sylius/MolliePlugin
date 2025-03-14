<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider\Divisor;

use Payum\Core\Request\GetCurrency;

interface DivisorProviderInterface
{
    public function getDivisor(): int;
    public function getDivisorForCurrency(GetCurrency $currency): int;
}
