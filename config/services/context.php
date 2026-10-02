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

use Sylius\MolliePlugin\Context\AdminUserContext;
use Sylius\MolliePlugin\Context\AdminUserContextInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.context.admin_user', AdminUserContext::class)
        ->public()
        ->args([service('security.token_storage')]);

    $services->alias(AdminUserContextInterface::class, 'sylius_mollie.context.admin_user');
};
