<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Menu\MollieRecurringMenuListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.menu_listener.mollie_recurring', MollieRecurringMenuListener::class)
        ->tag('kernel.event_listener', ['method' => 'buildMenu', 'event' => 'sylius.menu.admin.main']);
};
