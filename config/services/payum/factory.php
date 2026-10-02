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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryChecker;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Payum\Factory\CreateCustomerFactory;
use Sylius\MolliePlugin\Payum\Factory\CreateCustomerFactoryInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Payum\Resolver\ExistingMollieSessionResolver;
use Sylius\MolliePlugin\Payum\Resolver\ExistingMollieSessionResolverInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.payum.gateway_factory.mollie_gateway', MollieGatewayFactory::class);

    $services->set('sylius_mollie.payum.gateway_factory.mollie_subscription_gateway', MollieSubscriptionGatewayFactory::class);

    $services->set('sylius_mollie.payum.gateway_factory.builder.mollie', GatewayFactoryBuilder::class)
        ->args([service('sylius_mollie.payum.gateway_factory.mollie_gateway')])
        ->tag('payum.gateway_factory_builder', ['factory' => 'mollie']);

    $services->set('sylius_mollie.payum.gateway_factory.builder.mollie_subscription', GatewayFactoryBuilder::class)
        ->args([service('sylius_mollie.payum.gateway_factory.mollie_subscription_gateway')])
        ->tag('payum.gateway_factory_builder', ['factory' => 'mollie_subscription']);

    $services->set('sylius_mollie.payum.factory.create_customer', CreateCustomerFactory::class);

    $services->alias(CreateCustomerFactoryInterface::class, 'sylius_mollie.payum.factory.create_customer');

    $services->set('sylius_mollie.payum.checker.mollie_gateway_factory', MollieGatewayFactoryChecker::class)
        ->public();

    $services->alias(MollieGatewayFactoryCheckerInterface::class, 'sylius_mollie.payum.checker.mollie_gateway_factory');

    $services->set('sylius_mollie.payum.resolver.existing_mollie_session', ExistingMollieSessionResolver::class);

    $services->alias(ExistingMollieSessionResolverInterface::class, 'sylius_mollie.payum.resolver.existing_mollie_session');
};
