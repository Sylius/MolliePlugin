# UPGRADE FROM 3.3.3 TO 3.3.4

1. The checkout payment form no longer writes Mollie-specific keys (`molliePaymentMethods`,
   `cartToken`, `saveCardInfo`, `useSavedCards`) into `Payment::details` for payments made through
   non-Mollie gateways. The Mollie `details` sub-form is still added in `buildForm()`, but it is now:

   - removed when none of the payment methods available for the payment uses a Mollie gateway;
   - rebuilt as unmapped (`'mapped' => false`) when the submitted payment method does not use a
     Mollie gateway, so it still renders but never writes to the payment.

   Because Sylius reuses the same payment while its previous attempt is neither cancelled nor failed,
   switching away from a Mollie method would otherwise leave a real method id and card token behind on
   a payment another gateway is about to process. Those four keys are therefore dropped from
   `Payment::details` in exactly one case: the payment was stored on a Mollie method and the submitted
   method is not a Mollie one. Nothing else is ever removed, so data another integration keeps under
   the same names is left untouched.

   All of the above only ever acts on a `details` child that is a
   `Sylius\MolliePlugin\Form\Type\PaymentMollieType`. If another integration registers its own
   `details` form on the checkout payment type, that form is left alone.

2. `Sylius\MolliePlugin\Form\Extension\PaymentTypeExtension` gained an optional
   `Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface` constructor argument.
   It falls back to `Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryChecker` when omitted, so
   existing instantiations keep working unchanged.

# UPGRADE FROM 3.3.1 TO 3.3.2

1. Run `yarn install` and `yarn build` to rebuild the shop assets. The bundled
   `assets/shop/js/mollie/app.js` has changed and the shop will not work correctly until it is
   rebuilt.

2. The QR-code and thank-you shop endpoints no longer use the order `tokenValue` introduced in
   3.3.1. Ownership is now proven through the shop session, so both endpoints again accept
   `?orderId=` and validate it against the session:

   - `GET /{_locale}/get-code` serves the current session cart and rejects a foreign `orderId`
     with `HTTP 404`; its JSON response returns `orderId`.
   - `GET /{_locale}/thank-you` expects `?orderId=`, validates it against the session, and
     returns `HTTP 404` when it is missing or does not match.

   If you have overridden `app.js` or link to these endpoints yourself, switch back from
   `orderToken` to `orderId`.

3. QR-code payments can be paid while their order is still in the `cart` state, because the order
   is placed only after the payment completes. The payment state machine does not allow completing
   a payment from `cart`, so as a workaround the shop payment webhook applies the paid status
   directly in that case, otherwise QR-code orders would stay unpaid.

# UPGRADE FROM 3.3.0 TO 3.3.1

1. The shop payment webhook now verifies that the Mollie payment belongs to the referenced
   order before applying its status. Requests whose Mollie payment id does not match the id
   stored for the order are acknowledged with `HTTP 200` and ignored.

1. The thank-you and QR-code shop endpoints no longer accept a raw integer `orderId`; they now
   use the order's non-guessable `orderToken` (`tokenValue`):

   - `GET /{_locale}/thank-you` expects `?orderToken=` instead of `?orderId=` and returns
     `HTTP 404` when the token is missing or unknown.
   - `GET /{_locale}/get-code` expects `?orderToken=`, serves only the current session cart, and
     returns `orderToken` in its JSON response.

   The bundled `assets/shop/js/mollie/app.js` has been updated accordingly. If you have
   overridden that file or link to these endpoints yourself, switch from `orderId` to
   `orderToken`.

1. Run `yarn install` and `yarn build`.

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

   To support the transition to 4.0, two interfaces gained methods used by the migration process. If you provide
   your own implementations, add them (note that both interfaces are themselves deprecated and will be removed in
   4.0):

   `Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface`:
   - `findScheduledSubscriptionsForMigration(): array`
   - `iterateToMigrate(int $batchSize): iterable`
   - `findMigrated(int $limit): array`

   `Sylius\MolliePlugin\Entity\MollieSubscriptionInterface`:
   - `getMigratedAt(): ?\DateTime`
   - `setMigratedAt(?\DateTimeInterface $migratedAt): void`
   - `isMigrated(): bool`
