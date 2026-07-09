# UPGRADE FROM 2.2.8 TO 2.2.9

1. Run `yarn install` and `yarn build` to rebuild the shop assets. The bundled
   `assets/shop/js/mollie/app.js` has changed and the shop will not work correctly until it is
   rebuilt.

1. The QR-code and thank-you shop endpoints no longer use the order `tokenValue` introduced in
   2.2.8. Ownership is now proven through the shop session, so both endpoints again accept
   `?orderId=` and validate it against the session:

   - `GET /{_locale}/get-code` serves the current session cart and rejects a foreign `orderId`
     with `HTTP 403`; its JSON response returns `orderId`.
   - `GET /{_locale}/thank-you` expects `?orderId=`, validates it against the session, and
     returns `HTTP 404` when it is missing or does not match.

   If you have overridden `app.js` or link to these endpoints yourself, switch back from
   `orderToken` to `orderId`.

# UPGRADE FROM 2.2.7 TO 2.2.8

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

# UPGRADE FROM 2.1 TO 2.2

1. The following classes have been marked as deprecated:

   - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface`
   - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransitionInterface`
   - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransitionInterface`
   - `Sylius\MolliePlugin\EventListener\ProductVariantRecurringOptionsListener`
   - `Sylius\MolliePlugin\Form\Extension\CompleteTypeExtension`
   - `Sylius\MolliePlugin\Form\Type\DirectDebitType`
   - `Sylius\MolliePlugin\Processor\PaymentSurchargeProcessorInterface`

1. Winzou State Machine deprecations

   The constructors of the following classes have been changed:

   - `Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProvider`
   - `Sylius\MolliePlugin\Controller\Admin\RefundAction`
   - `Sylius\MolliePlugin\Controller\Shop\PayumController`
   - `Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicator`
   - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransition`
   - `Sylius\MolliePlugin\Console\Command\BeginProcessingSubscriptions`
   - `Sylius\MolliePlugin\Console\Command\ProcessSubscriptions`

      ```diff
      public function __construct(
          ...
      -   private readonly FactoryInterface $stateMachineFactory,
      +   private readonly FactoryInterface|StateMachineInterface $stateMachineFactory,
          ...
      ) {
      ```

1. The following constructor signatures have been changed:

   `Sylius\MolliePlugin\Payum\Action\Subscription\StatusRecurringSubscriptionAction`:
   ```diff
   public function __construct(
       private EntityManagerInterface $subscriptionManager,
       private SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator,
       private SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator,
   -   private StateMachineTransitionInterface $stateMachineTransition,
   +   private StateMachineTransitionInterface|StateMachineInterface $stateMachineTransition,
       )
   ```

   `Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator`:
   ```diff
   public function __construct(
        private MollieApiClient $mollieApiClient,
   -    private StateMachineTransitionInterface $stateMachineTransition,
   +    private StateMachineTransitionInterface|StateMachineInterface $stateMachineTransition,
   -    private PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
   +    private ?PaymentStateMachineTransitionInterface $paymentStateMachineTransition = null,
   -    private ProcessingStateMachineTransitionInterface $processingStateMachineTransition,
   +    private ?ProcessingStateMachineTransitionInterface $processingStateMachineTransition = null,
   )
   ```

   `Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator`:
   ```diff
   public function __construct(
   -    private StateMachineTransitionInterface $stateMachineTransition,
   +    private StateMachineTransitionInterface|StateMachineInterface $stateMachineTransition,
   -    private PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
   +    private ?PaymentStateMachineTransitionInterface $paymentStateMachineTransition = null,
   -    private ProcessingStateMachineTransitionInterface $processingStateMachineTransition,
   +    private ?ProcessingStateMachineTransitionInterface $processingStateMachineTransition = null,
   )
   ```

   `Sylius\MolliePlugin\Creator\AbandonedPaymentLinkCreator`:
    ```diff
    public function __construct(
        private readonly PaymentLinkResolverInterface $paymentLinkResolver,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
    -   private readonly ChannelContextInterface $channelContext,
    +   private readonly ChannelContextInterface|ChannelRepositoryInterface $channelContext,
    )
    ```

   `Sylius\MolliePlugin\StateMachine/Applicator/MollieOrderStatesApplicator`
   ```diff
   public function __construct(
        private readonly StateMachineInterface $stateMachine,
        private readonly OrderRepositoryInterface $orderRepository,
   -    private readonly CreatePartialShipFromMollieInterface $createPartialShipFromMollie,
   )
   ```

   `Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledChecker`
   ```diff
   public function __construct(
        private readonly RepositoryInterface $mollieGatewayConfigurationRepository,
   +    private readonly ?PaymentMethodsResolverInterface $paymentMethodsResolver = null,
   )
   ```
