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

namespace Sylius\MolliePlugin;

use Composer\InstalledVersions;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Sylius\MolliePlugin\DependencyInjection\SyliusMessageBusPolyfillPass;
use Sylius\Telemetry\TelemetryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SyliusMolliePlugin extends Bundle
{
    public const USER_AGENT_TOKEN = 'p5ACCDx8Tbn8vjpr';

    public static function getVersion(): string
    {
        $currentVersion = InstalledVersions::getPrettyVersion('sylius/mollie-plugin') ?? 'unknown';
        if (str_starts_with($currentVersion, 'v')) {
            return substr($currentVersion, 1);
        }

        return $currentVersion;
    }

    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SyliusMessageBusPolyfillPass());
        $container->addCompilerPass(new TelemetryCompilerPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
