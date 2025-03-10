<?php


declare(strict_types=1);

namespace SyliusMolliePlugin\Helper;

use SyliusMolliePlugin\Provider\Divisor\DivisorProviderInterface;

final class IntToStringConverter implements IntToStringConverterInterface
{
    public function __construct(private DivisorProviderInterface $divisorProvider)
    {
    }

    public function convertIntToString(int $value, ?int $divisor = null): string
    {
        if(null === $divisor)
        {
            $divisor = $this->divisorProvider->getDivisor();
        }

        return number_format(abs($value / $divisor), 2, '.', '');
    }
}
