# UPGRADE FROM 2.x TO 3.0

1. Support for Sylius 2.0 has been added, it is now the recommended Sylius version.

1. Support for Sylius 1.X has been dropped, upgrade your application to [Sylius 2.0](https://github.com/Sylius/Sylius/blob/2.0/UPGRADE-2.0.md).

1. Support for Symfony 5.4 has been dropped, the supported versions of Symfony are now ^6.4 || ^7.1.

1. The minimum supported version of PHP has been increased to 8.2.

1. The minimum supported version of SyliusRefundPlugin has been updated from ^1.5 to ^2.0.

1. The `sylius/admin-order-creation-plugin` integration layer has been removed as there are no plans of updating it to Sylius 2.0.

1. The following classes have been removed:

    - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition`
    - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface`
    - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransition`
    - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransitionInterface`
    - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransition`
    - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransitionInterface`

1. The following routes have been renamed:

   | Old Name                                           | New Name                                            |
   |----------------------------------------------------|-----------------------------------------------------|
   | `sylius_mollie_plugin_apple_pay_validation`        | `sylius_mollie_shop_apple_pay_validation`           |
   | `sylius_mollie_plugin_apple_pay_payment`           | `sylius_mollie_shop_apple_pay_payment`              |
   | `mollie_shop_checkout_apple_complete`              | `sylius_mollie_shop_checkout_apple_complete`        |
   | `sylius_mollie_plugin_credit_card_translation`     | `sylius_mollie_shop_credit_card_translation`        |
   | `sylius_mollie_plugin_thankYou_page_redirect`      | `sylius_mollie_shop_thank_you_page_redirect`        |
   | `sylius_mollie_plugin_payment_fee_calculate`       | `sylius_mollie_shop_payment_fee_calculate`          |
   | `sylius_mollie_plugin_payment_webhook`             | `sylius_mollie_shop_payment_webhook`                |
   | `sylius_mollie_plugin_payum`                       | `sylius_mollie_shop_payum`                          |
   | `sylius_mollie_plugin_qr_code`                     | `sylius_mollie_shop_qr_code`                        |
   | `sylius_mollie_plugin_get_qr_code`                 | `sylius_mollie_shop_get_qr_code`                    |
   | `sylius_mollie_plugin_remove_qr_code`              | `sylius_mollie_shop_remove_qr_code`                 |
   | `sylius_mollie_plugin_cancel_subscription_mollie`  | `sylius_mollie_shop_cancel_subscription_mollie`     |
   | `sylius_mollie_plugin_onboarding_wizard_completed` | `sylius_mollie_admin_onboarding_wizard_completed`   |
   | `sylius_mollie_plugin_onboarding_wizard_status`    | `sylius_mollie_admin_onboarding_wizard_status`      |
   | `mollie_admin_methods`                             | `sylius_mollie_admin_mollie_methods`                |
   | `mollie_admin_test_api_keys`                       | `sylius_mollie_admin_test_api_keys`                 |
   | `mollie_admin_change_position`                     | `sylius_mollie_admin_mollie_method_change_position` |
   | `mollie_admin_mollie_payment_method_delete_logo`   | `sylius_mollie_admin_mollie_method_delete_logo`     |
   | `sylius_mollie_plugin_mollie_subscription_show`    | `sylius_mollie_admin_mollie_subscription_show`      |
   | `sylius_mollie_plugin_mollie_subscription_cancel`  | `sylius_mollie_admin_mollie_subscription_cancel`    |
   | `sylius_mollie_plugin_mollie_subscription_pause`   | `sylius_mollie_admin_mollie_subscription_pause`     |
   | `sylius_mollie_plugin_mollie_subscription_resume`  | `sylius_mollie_admin_mollie_subscription_resume`    |
   | `sylius_mollie_plugin_payment_link`                | `sylius_mollie_admin_payment_link_generate`         |
   | `sylius_mollie_plugin_payum_refund`                | `sylius_mollie_admin_mollie_subscription_pause`     |

1. Winzou State Machine

   The constructors of the following classes have been changed:

    - `Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProvider`
    - `Sylius\MolliePlugin\Controller\Admin\RefundAction`
    - `Sylius\MolliePlugin\Controller\Shop\PayumController`
    - `Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicator`
    - `Sylius\MolliePlugin\Console\Command\BeginProcessingSubscriptions`
    - `Sylius\MolliePlugin\Console\Command\ProcessSubscriptions`

       ```diff
       public function __construct(
           ...
       -   private readonly FactoryInterface|StateMachineInterface $stateMachineFactory,
       +   private readonly StateMachineInterface $stateMachine,
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
   -   private StateMachineTransitionInterface|StateMachineInterface $stateMachineTransition,
   +   private StateMachineInterface $stateMachine,
       )
   ```

   `Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator`:
   ```diff
   public function __construct(
        private MollieApiClient $mollieApiClient,
   -    private StateMachineTransitionInterface|StateMachineInterface $stateMachineTransition,
   +    private StateMachineInterface $stateMachine,
   -    private ?PaymentStateMachineTransitionInterface $paymentStateMachineTransition = null,
   -    private ?ProcessingStateMachineTransitionInterface $processingStateMachineTransition = null,
   )
   ```

   `Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator`:
   ```diff
   public function __construct(
   -    private StateMachineTransitionInterface|StateMachineInterface $stateMachineTransition,
   +    private StateMachineInterface $stateMachine,
   -    private ?PaymentStateMachineTransitionInterface $paymentStateMachineTransition = null,
   -    private ?ProcessingStateMachineTransitionInterface $processingStateMachineTransition = null,
   )
   ```

1. The `Sylius\MolliePlugin\EventListener\ProductVariantRecurringOptionsListener` has been removed and the functionality is now provided by twig hooks.
