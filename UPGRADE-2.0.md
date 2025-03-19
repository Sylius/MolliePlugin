# UPGRADE FROM 1.x to 2.0

1. The following classes have been removed:
   - `Sylius\MolliePlugin\Repository\ProductVariantRepository`
   - `Sylius\MolliePlugin\Repository\ProductVariantRepositoryInterface`
   - `Sylius\MolliePlugin\Repository\CustomerRepository`
   - `Sylius\MolliePlugin\Repository\CustomerRepositoryInterface`

1. The `sylius/admin-order-creation-plugin` has been removed from required dependencies.
   It has been made purely optional, and an integration layer for it has been added.
   If you were using this plugin, you need to install it manually.

1. **Class and interface names have been updated.**

   The `OrderVoucherDistributor` class has been renamed to `OrderVouchersApplicator`, and its interface has also been updated:

    ```diff
    - final class OrderVoucherDistributor implements OrderVoucherDistributorInterface
    + final class OrderVouchersApplicator implements OrderVouchersApplicatorInterface
    ```

---

2. **The following files have been removed:**

    - `src/Checker/Version/MolliePluginLatestVersionChecker.php`
    - `src/Checker/Version/MolliePluginLatestVersionCheckerInterface.php`

---

3. **Changes in the `MollieGatewayConfig` entity:**

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

---

4. **Service ID changes:**

   The service ID `sylius_mollie_plugin.distributor.order.order_voucher_distributor` has been renamed to:

    ```diff
    - <service id="sylius_mollie_plugin.distributor.order.order_voucher_distributor" class="Sylius\MolliePlugin\Distributor\Order\OrderVoucherDistributor">
    + <service id="sylius_mollie_plugin.applicator.order.order_vouchers_applicator" class="Sylius\MolliePlugin\Applicator\Order\OrderVouchersApplicator">
    ```

   Additionally, the following service has been removed:

    ```xml
    <service id="sylius_mollie_plugin.checker.version.mollie_plugin_latest_version_checker" />
    ```

---

5. **Required database changes:**

   A Doctrine migration has already been added. To apply the changes, run:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

---

6. **Updating your code:**

   If your application uses `OrderVoucherDistributor`, `OrderVoucherDistributorInterface`, or the `orderExpiration` property, update all occurrences to their new names:

    - Replace `OrderVoucherDistributor` with `OrderVouchersApplicator`
    - Replace `OrderVoucherDistributorInterface` with `OrderVouchersApplicatorInterface`
    - Replace `orderExpiration` with `orderExpirationDays` in all entity references

---

7. **Removing dependency on `MolliePluginLatestVersionChecker`:**

   If your code relied on `MolliePluginLatestVersionChecker`, remove any references to it or replace it with `SyliusMolliePlugin::VERSION`.

---

