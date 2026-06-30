# UPGRADE FROM 3.2 TO 3.3

1. `Sylius\MolliePlugin\EventListener\ShipmentShipEventListener` and the `sylius_mollie.listener.shipment_ship` service
   (listening on `sylius.shipment.post_ship`) are deprecated and will be removed in 4.0.
   In 4.0, `Sylius\MolliePlugin\EventListener\Workflow\Shipment\ShipmentShipListener`
   (`sylius_mollie.listener.workflow.shipment.ship`, listening on `workflow.sylius_shipment.completed.ship`)
   will become the sole listener responsible for notifying Mollie about shipments - covering both admin UI and API contexts.
   Currently, the workflow listener only handles API requests; admin UI shipments are still handled by the old listener.

2. **Important update for projects using Mollie subscriptions!**

   Starting with Mollie Plugin 4.0, the subscription and recurring payment feature will be removed from the
   open-source Mollie integration. Standard Mollie payments will not be affected.

   As of 3.3, the subscription and recurring payment classes are marked as `@deprecated` and will be removed in 4.0.

   For migration details, please check the
   [dedicated blog post](https://sylius.com/blog/important-update-for-projects-using-mollie-subscriptions/)
   or [contact the Sylius team](https://sylius.com/contact/).

3. A nullable `migrated_at` column has been added to the `mollie_subscription` table. Run the Doctrine migrations
   after upgrading:

   ```bash
   bin/console doctrine:migrations:migrate
   ```
