<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\Component\Core\Filesystem\Adapter\FilesystemAdapterInterface;
use Sylius\MolliePlugin\Uploader\PaymentMethodLogoUploader;
use Sylius\MolliePlugin\Uploader\PaymentMethodLogoUploaderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.uploader.payment_method_logo', PaymentMethodLogoUploader::class)
        ->public()
        ->args([service(FilesystemAdapterInterface::class)]);

    $services->alias(PaymentMethodLogoUploaderInterface::class, 'sylius_mollie.uploader.payment_method_logo');
};
