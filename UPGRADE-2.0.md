# UPGRADE FROM 1.x TO 2.0

1. The following classes have been removed:
   - `Sylius\MolliePlugin\Checker\Version\MolliePluginLatestVersionChecker`
   - `Sylius\MolliePlugin\Checker\Version\MolliePluginLatestVersionCheckerInterface`
   - `Sylius\MolliePlugin\Repository\ProductVariantRepository`
   - `Sylius\MolliePlugin\Repository\ProductVariantRepositoryInterface`
   - `Sylius\MolliePlugin\Repository\CustomerRepository`
   - `Sylius\MolliePlugin\Repository\CustomerRepositoryInterface`

1. The `sylius/admin-order-creation-plugin` has been removed from required dependencies.
   It has been made purely optional, and an integration layer for it has been added.
   If you were using this plugin, you need to install it manually.

1. Class and interface names have been updated

   The `Sylius\MolliePlugin\Distributor\Order\OrderVoucherDistributor` class has been renamed
   to `Sylius\MolliePlugin\Applicator\OrderOrderVouchersApplicator` along with its interface.

1. Changes in `Sylius\MolliePlugin\Entity\MollieGatewayConfig`:

   The `orderExpiration` property has been renamed to `orderExpirationDays`:

    ```diff
    - protected $orderExpiration = 28;
    + protected $orderExpirationDays = 28;
    ```

   The getter and setter methods have been updated accordingly. Additionally, Doctrine mapping has been adjusted:

    ```diff
    - <field name="orderExpiration" column="order_expiration" type="integer" nullable="true" />
    + <field name="orderExpirationDays" column="order_expiration_days" type="integer" nullable="true" />
    ```

1. Service changes

   The service ID `sylius_mollie_plugin.distributor.order.order_voucher_distributor` has been renamed to:

    ```diff
    - <service id="sylius_mollie_plugin.distributor.order.order_voucher_distributor" class="Sylius\MolliePlugin\Distributor\Order\OrderVoucherDistributor">
    + <service id="sylius_mollie_plugin.applicator.order.order_vouchers" class="Sylius\MolliePlugin\Applicator\Order\OrderVouchersApplicator">
    ```

1. Removed services:
   - `sylius_mollie_plugin.checker.version.mollie_plugin_latest_version_checker`
