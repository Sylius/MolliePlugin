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

use Sylius\MolliePlugin\Filter\MollieMethodFilter;
use Sylius\MolliePlugin\Filter\MollieMethodFilterInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.filter.mollie_method', MollieMethodFilter::class);

    $services->alias(MollieMethodFilterInterface::class, 'sylius_mollie.filter.mollie_method');
};
