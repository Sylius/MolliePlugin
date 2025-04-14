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

use Sylius\MolliePlugin\Provider\DivisorProviderInterface;

final class IntToStringConverter implements IntToStringConverterInterface
{
    public function __construct(private readonly DivisorProviderInterface $divisorProvider)
    {
    }

    public function convertIntToString(int $value, ?int $divisor = null): string
    {
        if (null === $divisor) {
            $divisor = $this->divisorProvider->getDivisor();
        }

        return number_format(abs($value / $divisor), 2, '.', '');
    }
}
