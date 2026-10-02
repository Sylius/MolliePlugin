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

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Client\Parser\ApiExceptionParser;
use Sylius\MolliePlugin\Client\Parser\ApiExceptionParserInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.client.mollie_api', MollieApiClient::class);

    $services->set('sylius_mollie.client.parser.api_exception', ApiExceptionParser::class)
        ->public();

    $services->alias(ApiExceptionParserInterface::class, 'sylius_mollie.client.parser.api_exception');
};
