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

namespace Sylius\MolliePlugin\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SyliusMessageBusPolyfillPass implements CompilerPassInterface
{
    public const ID_FALLBACK = [
        'sylius.command_bus' => 'sylius_default.bus',
    ];

    public const COMMAND_BUS_ALIAS = 'sylius_mollie.command_bus';

    public function process(ContainerBuilder $container): void
    {
        $buses = array_keys($container->findTaggedServiceIds('messenger.bus'));
        $this->setupDefaultCommandBus($buses, $container);
    }

    /** @param string[] $buses */
    private function setupDefaultCommandBus(array $buses, ContainerBuilder $container): void
    {
        $targetBusName = in_array('sylius.command_bus', $buses, true) ? 'sylius.command_bus' : 'sylius_default.bus';
        $container->setAlias(
            self::COMMAND_BUS_ALIAS,
            $targetBusName,
        );
    }
}
