# UPGRADE FROM 3.2 TO 3.3

1. `Sylius\MolliePlugin\EventListener\ShipmentShipEventListener` and the `sylius_mollie.listener.shipment_ship` service
   (listening on `sylius.shipment.post_ship`) are deprecated and will be removed in 4.0.
   In 4.0, `Sylius\MolliePlugin\EventListener\Workflow\Shipment\ShipmentShipListener`
   (`sylius_mollie.listener.workflow.shipment.ship`, listening on `workflow.sylius_shipment.completed.ship`)
   will become the sole listener responsible for notifying Mollie about shipments - covering both admin UI and API contexts.
   Currently, the workflow listener only handles API requests; admin UI shipments are still handled by the old listener.
