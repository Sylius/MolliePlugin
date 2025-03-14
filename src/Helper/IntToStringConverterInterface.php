<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Helper;

interface IntToStringConverterInterface
{
    public function convertIntToString(int $value, ?int $divisor = null): string;
}
