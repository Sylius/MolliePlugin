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

namespace SyliusMolliePlugin\Twig\Extension;

use SyliusMolliePlugin\Checker\ApplePay\ApplePayEnabledCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ApplePayDirectEnabled extends AbstractExtension
{
    /** @var ApplePayEnabledCheckerInterface */
    private $applePayEnabledChecker;

    public function __construct(ApplePayEnabledCheckerInterface $applePayEnabledChecker)
    {
        $this->applePayEnabledChecker = $applePayEnabledChecker;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'mollie_render_apple_pay_direct',
                [$this->applePayEnabledChecker, 'isEnabled'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
