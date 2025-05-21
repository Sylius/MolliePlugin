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

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Sylius\MolliePlugin\DependencyInjection\SyliusMessageBusPolyfillPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SyliusMolliePlugin extends Bundle
{
    public const VERSION = '5.0.0';

    public const USER_AGENT_TOKEN = 'p5ACCDx8Tbn8vjpr';

    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SyliusMessageBusPolyfillPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
