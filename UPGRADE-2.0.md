# UPGRADE FROM 1.x to 2.0

1. The following classes have been removed:
   - `Sylius\MolliePlugin\Repository\ProductVariantRepository`
   - `Sylius\MolliePlugin\Repository\ProductVariantRepositoryInterface`
   - `Sylius\MolliePlugin\Repository\CustomerRepository`
   - `Sylius\MolliePlugin\Repository\CustomerRepositoryInterface`

1. The `sylius/admin-order-creation-plugin` has been removed from required dependencies.
   It has been made purely optional, and an integration layer for it has been added.
   If you were using this plugin, you need to install it manually.
