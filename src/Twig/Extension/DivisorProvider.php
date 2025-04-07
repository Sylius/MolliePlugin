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

namespace Sylius\MolliePlugin\Twig\Extension;

use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DivisorProvider extends AbstractExtension
{
    public function __construct(private readonly DivisorProviderInterface $divisorProvider)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'mollie_get_divisor',
                [$this->divisorProvider, 'getDivisor'],
                ['is_safe' => ['html']],
            ),
        ];
    }
}
