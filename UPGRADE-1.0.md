# UPGRADE FROM 0.x to 1.0

1. Support for Sylius 1.9 has been dropped, upgrade your application to [Sylius 1.13](https://github.com/Sylius/Sylius/blob/1.13/UPGRADE.md).

1. The minimum supported version of PHP has been increased to 8.1.

1. Due to compatibility issues the configs of the Sylius Refund Plugin
   have been removed from internal configuration of the plugin.
   You need to add them manually:

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
