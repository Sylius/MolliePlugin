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

use Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ApplePayDirectEnabled extends AbstractExtension
{
    public function __construct(private readonly ApplePayEnabledCheckerInterface $applePayEnabledChecker)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'mollie_render_apple_pay_direct',
                [$this->applePayEnabledChecker, 'isEnabled'],
                ['is_safe' => ['html']],
            ),
        ];
    }
}
