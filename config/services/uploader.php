<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Gaufrette\Filesystem;
use Sylius\MolliePlugin\Uploader\PaymentMethodLogoUploader;
use Sylius\MolliePlugin\Uploader\PaymentMethodLogoUploaderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.uploader.payment_method_logo', PaymentMethodLogoUploader::class)
        ->public()
        ->args([inline_service(Filesystem::class)
            ->args(['%sylius.uploader.filesystem%'])
            ->factory([service('knp_gaufrette.filesystem_map'), 'get'])]);

    $services->alias(PaymentMethodLogoUploaderInterface::class, 'sylius_mollie.uploader.payment_method_logo');
};
