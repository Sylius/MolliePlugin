<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Form\Transformer\MollieIntervalTransformer;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.form.type.data_transformer.mollie_interval', MollieIntervalTransformer::class);
};
