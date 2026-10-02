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

use Sylius\MolliePlugin\Fixture\Listener\ProductsWithinAllChannelsListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.fixture.listener.products_within_all_channels', ProductsWithinAllChannelsListener::class)
        ->args([
            service('sylius.repository.channel'),
            service('sylius.repository.product'),
            service('doctrine.orm.entity_manager'),
        ])
        ->tag('sylius_fixtures.listener');
};
