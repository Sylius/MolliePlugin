# UPGRADE FROM 3.3 TO 3.4

1. `Sylius\MolliePlugin\Uploader\PaymentMethodLogoUploader` no longer depends on `Gaufrette\Filesystem`.
   It is now constructed with `Sylius\Component\Core\Filesystem\Adapter\FilesystemAdapterInterface`
   (backed by Flysystem, resolved to the same `sylius.adapter.filesystem.default` storage already
   used by Sylius core for images), and the `sylius_mollie.uploader.payment_method_logo` service
   definition has been updated accordingly. This removes the plugin's dependency on
   `knplabs/knp-gaufrette-bundle`, which Sylius core is dropping.

   If you have decorated or otherwise redefined the `sylius_mollie.uploader.payment_method_logo`
   service and pass it a `Gaufrette\Filesystem` argument, update it to inject
   `Sylius\Component\Core\Filesystem\Adapter\FilesystemAdapterInterface` instead. Stored logo files
   are unaffected, as both filesystems resolve to the same directory.
