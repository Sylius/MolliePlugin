<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Subscription\Guard\SubscriptionGuard;
use Sylius\MolliePlugin\Subscription\Guard\SubscriptionGuardInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.subscription.guard.subscription', SubscriptionGuard::class)
        ->public();

    $services->alias(SubscriptionGuardInterface::class, 'sylius_mollie.subscription.guard.subscription');
};
