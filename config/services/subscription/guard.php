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

use Sylius\MolliePlugin\Subscription\Guard\SubscriptionGuard;
use Sylius\MolliePlugin\Subscription\Guard\SubscriptionGuardInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.subscription.guard.subscription', SubscriptionGuard::class)
        ->public();

    $services->alias(SubscriptionGuardInterface::class, 'sylius_mollie.subscription.guard.subscription');
};
