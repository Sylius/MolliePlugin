<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Form\Type\MollieLoggerLevelFilterType;
use Sylius\MolliePlugin\Form\Type\MollieSubscriptionStateGridFilterType;
use Sylius\MolliePlugin\Grid\Filter\MollieLoggerLevel;
use Sylius\MolliePlugin\Grid\Filter\MollieSubscriptionState;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.grid.filter.mollie_logger_level', MollieLoggerLevel::class)
        ->tag('sylius.grid_filter', ['type' => 'log_level', 'form_type' => MollieLoggerLevelFilterType::class]);

    $services->set('sylius_mollie.grid.filter.mollie_subscription_state', MollieSubscriptionState::class)
        ->tag('sylius.grid_filter', ['type' => 'subscription_state', 'form_type' => MollieSubscriptionStateGridFilterType::class]);
};
