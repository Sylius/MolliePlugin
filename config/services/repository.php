<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Repository\Query\AbandonedOrdersQuery;
use Sylius\MolliePlugin\Repository\Query\AbandonedOrdersQueryInterface;
use Sylius\MolliePlugin\Repository\Query\MollieBasedPaymentMethodQuery;
use Sylius\MolliePlugin\Repository\Query\MollieBasedPaymentMethodQueryInterface;
use Sylius\MolliePlugin\Repository\Query\OrderByTokenForAvailableMethodsQuery;
use Sylius\MolliePlugin\Repository\Query\OrderByTokenForAvailableMethodsQueryInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.repository.query.order.abandoned', AbandonedOrdersQuery::class)
        ->args([service('sylius.repository.order')]);

    $services->alias(AbandonedOrdersQueryInterface::class, 'sylius_mollie.repository.query.order.abandoned');

    $services->set('sylius_mollie.repository.query.payment_method.mollie_based', MollieBasedPaymentMethodQuery::class)
        ->args([service('sylius.repository.payment_method')]);

    $services->alias(MollieBasedPaymentMethodQueryInterface::class, 'sylius_mollie.repository.query.payment_method.mollie_based');

    $services->set('sylius_mollie.repository.query.order.by_token_for_available_methods', OrderByTokenForAvailableMethodsQuery::class)
        ->args([service('sylius.repository.order')]);

    $services->alias(OrderByTokenForAvailableMethodsQueryInterface::class, 'sylius_mollie.repository.query.order.by_token_for_available_methods');
};
