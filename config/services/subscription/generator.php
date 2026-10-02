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

use Sylius\MolliePlugin\Subscription\Generator\SubscriptionScheduleGenerator;
use Sylius\MolliePlugin\Subscription\Generator\SubscriptionScheduleGeneratorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.subscription.generator.subscription_schedule', SubscriptionScheduleGenerator::class)
        ->public()
        ->args([
            service('sylius_mollie.factory.date_period'),
            service('sylius_mollie.custom_factory.mollie_subscription_schedule'),
        ]);

    $services->alias(SubscriptionScheduleGeneratorInterface::class, 'sylius_mollie.subscription.generator.subscription_schedule');
};
