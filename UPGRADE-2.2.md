# UPGRADE FROM 2.1 TO 2.2

1. The following classes have been marked as deprecated:

   - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface`
   - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransitionInterface`
   - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransitionInterface`
   - `Sylius\MolliePlugin\EventListener\ProductVariantRecurringOptionsListener`

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
