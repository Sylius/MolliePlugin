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

use Tests\Sylius\MolliePlugin\Behat\Client\TestMollieApiClient;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $container->import('services/contexts.yml');
    $container->import('services/pages.yml');

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.client.mollie_api', TestMollieApiClient::class);
};
