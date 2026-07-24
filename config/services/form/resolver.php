<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Form\Resolver\ProductVariantValidationGroupsResolver;
use Sylius\MolliePlugin\Form\Resolver\ValidationGroupsResolverInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.form.resolver.product_variant_validation_groups', ProductVariantValidationGroupsResolver::class);

    $services->alias(ValidationGroupsResolverInterface::class, 'sylius_mollie.form.resolver.product_variant_validation_groups');
};
