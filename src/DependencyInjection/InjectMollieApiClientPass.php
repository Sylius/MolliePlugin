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

use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class InjectMollieApiClientPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('payum.builder')) {
            return;
        }

        $payumBuilder = $container->findDefinition('payum.builder');

        foreach ([MollieGatewayFactory::FACTORY_NAME, MollieSubscriptionGatewayFactory::FACTORY_NAME] as $factoryName) {
            $payumBuilder->addMethodCall('addGatewayFactoryConfig', [
                $factoryName,
                ['payum.http_client' => new Reference('sylius_mollie.client.mollie_api')],
            ]);
        }
    }
}
