# UPGRADE FROM 3.3 TO 3.4

1. A Mollie payment reported as `open` now leaves the Sylius payment in `new` instead of moving it
   to `processing`. In the Payum status flow, `processing` is now reached only from Mollie's
   `pending`.

   Anything that treats `processing` as "the customer started paying" changes meaning: admin grid
   filters, reporting, exports and custom state-machine callbacks.

2. `ConvertMolliePaymentAction` now copies `payment_mollie_id` and `order_mollie_id` from the
   existing payment details into its result. If you decorate this action, keep both keys in the
   returned array.

3. `CaptureAction` takes a resolver deciding what to do with a Mollie session already tracked on the
   payment: leave it to the status flow, hand it back to the customer, or replace it.

   ```diff
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private PaymentRepositoryInterface $paymentRepository,
   +    private ExistingMollieSessionResolverInterface $existingSessionResolver,
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
    ) {
   ```

5. Once an order has been placed, only methods whose surcharge matches the one already charged are
   offered when changing the payment method. Shops that configure different surcharges per method
   will see a shorter list there than before. Nothing changes during checkout.

6. The payment fee calculators in `Sylius\MolliePlugin\Calculator\PaymentFee` also implement
   `PaymentSurchargeAmountCalculatorInterface`, which reports a surcharge instead of applying it
   to an order. `PaymentSurchargeCalculatorInterface` is unchanged, so existing implementations
   keep working.

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
