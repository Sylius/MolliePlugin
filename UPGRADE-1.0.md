# UPGRADE FROM 0.x to 1.0

1. Support for Sylius 1.9 and 1.10 have been dropped, upgrade your application to [Sylius 1.13](https://github.com/Sylius/Sylius/blob/1.13/UPGRADE.md).

1. The minimum supported version of PHP has been increased to 8.1.

1. Due to compatibility issues the configs of the Sylius Refund Plugin have been removed from internal 
   configuration of the plugin. You need to add them manually:

   ```yaml
   # config/packages/_sylius.yaml
   imports:
       ...
       - { resource: "@SyliusRefundPlugin/Resources/config/app/config.yml" }
   ```
   and    
   ```yaml
   # config/routes.yaml
   sylius_refund:
       resource: "@SyliusRefundPlugin/Resources/config/routing.yml"
   ```
   Both of these should be added **before** the MolliePlugin configuration.

1. The `sylius_mollie_plugin_apple_pay_payment` route has been removed, as its controller does not exist.

1. The override of `sylius_refund_refund_units` route has been removed, as its controller does not exist. 
   The route itself is still present with its initial logic from the Sylius Refund Plugin.

1. The methods `getSubscription` and `setSubsscription` have been removed from `Sylius\MolliePlugin\Entity\OrderInterface` and `Sylius\MolliePlugin\Entity\RecurringOrderTrait`.
   There was an incorrect relationship between the `Order` and `MollieSubscription` entities. The `Order` entity had a many-to-one relationship to `MollieSubscription` entity,
   while the `MollieSubscription` entity had a many-to-many relationship with the `Order` entity. Now, only a unidirectional many-to-many relationship remains
   between the `MollieSubscription` and `Order` entities.
