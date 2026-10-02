<?php

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
