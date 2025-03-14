<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin;

use Sylius\MolliePlugin\DependencyInjection\SyliusMessageBusPolyfillPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
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
}
