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

use Sylius\MolliePlugin\SyliusMolliePlugin;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MolliePluginLatestVersion extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'mollie_render_version_widget',
                [$this, 'versionRenderWidget'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ],
            ),
        ];
    }

    public function versionRenderWidget(Environment $environment): string
    {
        $latestVersion = str_replace('v', '', SyliusMolliePlugin::VERSION);

        if (SyliusMolliePlugin::VERSION === $latestVersion) {
            return '';
        }

        return $environment->render('@SyliusAdmin/PaymentMethod/_versionNotification.html.twig', [
            'latestVersion' => $latestVersion,
            'currentVersion' => SyliusMolliePlugin::VERSION,
        ]);
    }
}
