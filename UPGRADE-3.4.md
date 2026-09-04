# UPGRADE FROM 3.3 TO 3.4

1. A Mollie payment reported as `open` now leaves the Sylius payment in `new` instead of moving it
   to `processing`. In the Payum status flow, `processing` is now reached only from Mollie's
   `pending`.

   Anything that treats `processing` as "the customer started paying" changes meaning: admin grid
   filters, reporting, exports and custom state-machine callbacks.

2. `ConvertMolliePaymentAction` now copies everything describing the Mollie session already tracked
   on the payment into its result: `payment_mollie_id`, `order_mollie_id`, `webhookUrl`, `backurl`
   and `metadata.refund_token`. Payum replaces the payment details with this result on every capture
   while the payment sits in `new`, so a decorating action that drops these keys loses the payment
   link, the abandoned payment link emails and Mollie side refunds for that payment.

   `ConvertMollieSubscriptionPaymentAction` carries the same four top level keys over.

3. `CaptureAction` takes a resolver deciding what to do with a Mollie session already tracked on the
   payment: leave it to the status flow, hand it back to the customer, or replace it. It also takes
   the logger, and records the two Mollie failures it used to swallow: a tracked session it cannot
   read, and a superseded session it cannot cancel.

   ```diff
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private PaymentRepositoryInterface $paymentRepository,
   +    private ExistingMollieSessionResolverInterface $existingSessionResolver,
   +    private MollieLoggerActionInterface $loggerAction,
    ) {
   ```

4. `MolliePaymentsMethodResolver` takes the surcharge amount calculator.

   ```diff
    public function __construct(
        private readonly MollieGatewayConfigRepository $mollieGatewayRepository,
        private readonly MollieCountriesRestrictionResolverInterface $countriesRestrictionResolver,
        private readonly ProductVoucherTypeCheckerInterface $productVoucherTypeChecker,
        private readonly PaymentCheckoutOrderResolverInterface $paymentCheckoutOrderResolver,
        private readonly MollieBasedPaymentMethodQueryInterface $mollieBasedPaymentMethodQuery,
        private readonly MollieAllowedMethodsResolverInterface $allowedMethodsResolver,
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly MollieFactoryNameResolverInterface $mollieFactoryNameResolver,
        private readonly DivisorProviderInterface $divisorProvider,
   +    private readonly PaymentSurchargeAmountCalculatorInterface $surchargeAmountCalculator,
   +    private readonly PaymentSurchargeAdjustmentsProviderInterface $surchargeAdjustmentsProvider,
    ) {
   ```

5. Once an order has been placed, only methods whose surcharge matches the one already charged are
   offered when changing the payment method. Shops that configure different surcharges per method
   will see a shorter list there than before. Nothing changes during checkout.

   When a surcharge cannot be compared, only the method the order already carries is offered and the
   reason is logged, so the total stays correct and the method list never fails because of it. See
   point 6.

6. The payment fee calculators in `Sylius\MolliePlugin\Calculator\PaymentFee` also implement
   `PaymentSurchargeAmountCalculatorInterface`, which reports a surcharge instead of applying it
   to an order. `PaymentSurchargeCalculatorInterface` is unchanged and was deliberately left alone
   rather than gaining the new method, because a class implementing it without `calculateAmount()`
   would stop loading altogether.

   So a custom calculator implementing only `PaymentSurchargeCalculatorInterface` keeps applying its
   surcharge exactly as before and needs no change to keep working. What it cannot do is report an
   amount, so the plugin cannot compare its surcharge against the one already on an order. After
   checkout completion such an order is then offered only the method it already carries, which is
   the one that produced its surcharge, and the reason is logged.

   To take part in the comparison, implement `PaymentSurchargeAmountCalculatorInterface` as well and
   have `calculate()` delegate to `calculateAmount()`, which is what the bundled calculators do, so
   the applied and the reported value cannot drift apart.

   Calculated amounts are unchanged. `FixedAmountAndPercentageCalculator` no longer adds and then
   removes intermediate adjustments to arrive at its total, so an order carrying unrelated
   `fixed_fee` or `percentage` adjustments is no longer affected by it.

   Its second and third constructor arguments are now typed
   `PaymentSurchargeAmountCalculatorInterface` instead of `PaymentSurchargeCalculatorInterface`.
   Passing a calculator that implements only the latter no longer type checks.

   ```diff
    public function __construct(
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
   -    private readonly PaymentSurchargeCalculatorInterface $percentageCalculator,
   -    private readonly PaymentSurchargeCalculatorInterface $fixedAmountCalculator,
   +    private readonly PaymentSurchargeAmountCalculatorInterface $percentageCalculator,
   +    private readonly PaymentSurchargeAmountCalculatorInterface $fixedAmountCalculator,
        private readonly DivisorProviderInterface $divisorProvider,
    ) {
   ```

7. The log entry written when a paid Mollie payment is not the one being tracked is now recorded at
   error level rather than as a notice, and its wording changed.

8. That same log entry is no longer written for Order API payment webhooks, where Mollie calls the
   notify token with the `tr_` id of the payment inside the order.

9. `PaymentSurchargeAdjustmentsProviderInterface` is the single source of truth for the adjustment
   types a payment surcharge can produce. `PaymentFeeAdjustmentClearer` reads them from it rather
   than naming three types itself.

   A new parameter has been introduced, `sylius_mollie.payment_surcharge_adjustments`, holding the
   three built in types. Redefine it to have your own surcharge adjustments cleared and compared
   along with them.

   ```diff
   +public function __construct(
   +    private readonly PaymentSurchargeAdjustmentsProviderInterface $surchargeAdjustmentsProvider,
   +) {
   +}
   +
    public function clear(OrderInterface $order): void
   ```

   `PaymentFeeCalculateAction::PAYMENTS_FEE_METHOD` still holds the same three types and still
   works, but the provider is what the plugin now reads.

9. `Sylius\MolliePlugin\Uploader\PaymentMethodLogoUploader` no longer depends on `Gaufrette\Filesystem`.
   It is now constructed with `Sylius\Component\Core\Filesystem\Adapter\FilesystemAdapterInterface`
   (backed by Flysystem, resolved to the same `sylius.adapter.filesystem.default` storage already
   used by Sylius core for images), and the `sylius_mollie.uploader.payment_method_logo` service
   definition has been updated accordingly. This removes the plugin's dependency on
   `knplabs/knp-gaufrette-bundle`, which Sylius core is dropping.

   If you have decorated or otherwise redefined the `sylius_mollie.uploader.payment_method_logo`
   service and pass it a `Gaufrette\Filesystem` argument, update it to inject
   `Sylius\Component\Core\Filesystem\Adapter\FilesystemAdapterInterface` instead. Stored logo files
   are unaffected, as both filesystems resolve to the same directory.
