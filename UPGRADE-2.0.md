# UPGRADE FROM 1.x TO 2.0

1. The following classes have been removed:
   - `Sylius\MolliePlugin\Checker\Version\MolliePluginLatestVersionChecker`
   - `Sylius\MolliePlugin\Checker\Version\MolliePluginLatestVersionCheckerInterface`
   - `Sylius\MolliePlugin\Repository\ProductVariantRepository`
   - `Sylius\MolliePlugin\Repository\ProductVariantRepositoryInterface`
   - `Sylius\MolliePlugin\Repository\CustomerRepository`
   - `Sylius\MolliePlugin\Repository\CustomerRepositoryInterface`

1. The `sylius/admin-order-creation-plugin` has been removed from required dependencies.
   It has been made purely optional, and an integration layer for it has been added.
   If you were using this plugin, you need to install it manually.

1. Class and interface names have been updated

   The `Sylius\MolliePlugin\Distributor\Order\OrderVoucherDistributor` class has been renamed
   to `Sylius\MolliePlugin\Voucher\Applicator\OrderOrderVouchersApplicator` along with its interface.

1. The `Sylius\MolliePlugin\PaymentFee\Types\SurchargeTypeInterface` has been refactored into
   `Sylius\MolliePlugin\Calculator\PaymentFee\PaymentSurchargeCalculatorInterface` along with all its implementations. This includes:
    - `Sylius\MolliePlugin\PaymentFee\Types\FixedAmount` => `Sylius\MolliePlugin\Calculator\PaymentFee\FixedAmountCalculator`
    - `Sylius\MolliePlugin\PaymentFee\Types\Percentage` => `Sylius\MolliePlugin\Calculator\PaymentFee\PercentageCalculator`
    - `Sylius\MolliePlugin\PaymentFee\Types\FixedAmountAndPercentage` => `Sylius\MolliePlugin\Calculator\PaymentFee\FixedAmountAndPercentageCalculator`

   The interface itself has also been slightly changed:
    - method `::canCalculate` has been renamed to `::supports`
    - method `::calculate` does not have a return value anymore

   The previous service `sylius_mollie_plugin.payment_surcharge.calculate` has been refactored into `sylius_mollie.calculator.payment_fee.composite` which also implements the new `PaymentSurchargeCalculatorInterface`. Instead of static dependency to a few calculators, it's now a composite consisting of all calculators tagged with `sylius_mollie.payment_fee.calculator`.

1. The methods `getSubscription` and `setSubsscription` have been removed from `Sylius\MolliePlugin\Entity\OrderInterface` and `Sylius\MolliePlugin\Entity\RecurringOrderTrait`.
   There was an incorrect relationship between the `Order` and `MollieSubscription` entities. The `Order` entity had a many-to-one relationship to `MollieSubscription` entity,
   while the `MollieSubscription` entity had a many-to-many relationship with the `Order` entity. Now, only a unidirectional many-to-many relationship remains
   between the `MollieSubscription` and `Order` entities.

1. Changes in `Sylius\MolliePlugin\Entity\MollieCustomer`:
   
   The `profileId` property has been marked as nullable:

    ```diff
    - protected string $profileId;
    + protected ?string $profileId = null;
    ```

1. Changes in `Sylius\MolliePlugin\Entity\MollieGatewayConfig`:

   The `orderExpiration` property has been renamed to `orderExpirationDays`:

    ```diff
    - protected $orderExpiration = 28;
    + protected $orderExpirationDays = 28;
    ```

   The getter and setter methods have been updated accordingly. Additionally, Doctrine mapping has been adjusted:

    ```diff
    - <field name="orderExpiration" column="order_expiration" type="integer" nullable="true" />
    + <field name="orderExpirationDays" column="order_expiration_days" type="integer" nullable="true" />
    ```

1. Service changes

   The service ID `sylius_mollie_plugin.distributor.order.order_voucher_distributor` has been renamed to:

    ```diff
    - <service id="sylius_mollie_plugin.distributor.order.order_voucher_distributor" class="Sylius\MolliePlugin\Distributor\Order\OrderVoucherDistributor">
    + <service id="sylius_mollie.voucher.applicator.order_vouchers" class="Sylius\MolliePlugin\Voucher\Applicator\OrderVouchersApplicator">
    ```

1. Constructor changes

   The constructor of `Sylius\MolliePlugin\Voucher\Updater\OrderVoucherAdjustmentUpdater` has been changed:

    ```diff
    public function __construct(
        RepositoryInterface $orderRepository,
    -   AdjustmentFactoryInterface $adjustmentFactory,
        OrderVouchersApplicatorInterface $orderVouchersApplicator,
        DivisorProviderInterface $divisorProvider,
    )
    ```

   The constructor of `Sylius\MolliePlugin\Controller\Admin\OnboardingWizard\CompletedAction` has been changed:

    ```diff
    use Doctrine\ORM\EntityManagerInterface;
    use Sylius\MolliePlugin\Context\AdminUserContextInterface;

    public function __construct(
    -   StatusCreatorInterface $onboardingWizardStatusCreator,
    +   AdminUserContextInterface $adminUserContext,
    +   EntityManagerInterface $entityManager,
    )
    ```

   The constructor of `Sylius\MolliePlugin\Controller\Admin\OnboardingWizard\StatusAction` has been changed:

    ```diff
    public function __construct(
    -   RepositoryInterface $statusRepository,
        AdminUserContextInterface $adminUserContext,
    )
    ```

   The constructor of `Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectProvider` has been changed:
   
   ```diff
   use Sylius\MolliePlugin\ApplePay\Resolver\AddressResolverInterface;

   public function __construct(
   -   ApplePayAddressResolverInterface $applePayAddressResolver,
   +   AddressResolverInterface $applePayAddressResolver,
   -   MollieApiClient $mollieApiClient,
       OrderPaymentApplePayDirectProviderInterface $paymentApplePayDirectProvider,
       CustomerProviderInterface|OrderCreationCustomerProviderInterface $customerProvider,
       ApplePayDirectPaymentProviderInterface $applePayDirectPaymentProvider,
   )
   ```

   The constructor of `Sylius\MolliePlugin\Order\ShipmentCloner` has been changed:
   
   ```diff
   public function __construct(
       FactoryInterface $shipmentFactory,
   -   ShipmentUnitClonerInterface $shipmentUnitCloner,
   )
   ```

   The constructor of `Sylius\MolliePlugin\Controller\Admin\GeneratePaymentLinkAction` has been changed:
   
   ```diff
   public function __construct(
       OrderRepositoryInterface $orderRepository,
       Environment $twig,
       RequestStack $requestStack,
       UrlGeneratorInterface $router,
       FormFactoryInterface $formFactory,
   -   MollieApiClient $mollieApiClient,
       PaymentLinkResolverInterface $paymentLinkResolver,
       MollieLoggerActionInterface $loggerAction,
   )
   ```

   The constructor of `Sylius\MolliePlugin\Controller\Shop\PayumController` has been changed:
   
   ```diff
   public function __construct(
       Payum $payum,
       OrderRepositoryInterface $orderRepository,
   -   MetadataInterface $orderMetadata,
   -   RequestConfigurationFactoryInterface $requestConfigurationFactory,
       RouterInterface $router,
       FactoryInterface $stateMachineFactory,
       EntityManagerInterface $entityManager,
   )
   ```

   The constructor of `Sylius\MolliePlugin\Creator\AbandonedPaymentLinkCreator` has been changed:
   
   ```diff
   public function __construct(
       PaymentLinkResolverInterface $paymentLinkResolver,
       OrderRepositoryInterface $orderRepository,
   -   PaymentLinkEmailManagerInterface $paymentLinkEmailManager,
       PaymentMethodRepositoryInterface $paymentMethodRepository,
       ChannelContextInterface $channelContext,
   )
   ```

   The constructor of `Sylius\MolliePlugin\Refund\Creator\PaymentRefundCommandCreator` has been changed:
   
   ```diff
   public function __construct(
       RepositoryInterface $orderRepository,
       RepositoryInterface $refundUnitsRepository,
       PaymentUnitsItemRefundInterface $itemRefund,
       ShipmentUnitRefundInterface $shipmentRefund,
   -   AdjustmentFactoryInterface $adjustmentFactory,
       RefundPaymentMethodsProviderInterface $refundPaymentMethodProvider,
       DivisorProviderInterface $divisorProvider,
   )
   ```

   The constructor of `Sylius\MolliePlugin\Twig\Extension\CustomerCreditCards` has been changed:
   
   ```diff
   public function __construct(
   -   MollieApiClient $apiClient,
       EntityRepository $customerRepository,
       CustomerContextInterface $customerContext,
   )
   ```

   The constructor of `Sylius\MolliePlugin\Resolver\MolliePaymentMethodImageResolver` has been removed.

1. Method signature changes:

   The `::provide` method of `ValidationGroupsResolverInterface` has been renamed to `::resolve`.
   The `::prepare` method of `PaymentLinkEmailManagerInterface` has been renamed to `::send`.
   The `::createFromRequest(Request $request)` method of `ChangePositionPaymentMethodCreatorInterface` has been refactored into `::update(array $positions)`.

1. The entity `Sylius\MolliePlugin\Entity\OnboardingWizardStatus` along with its resource configuration and related classes have been removed.
   The onboarding status itself has been moved to a field `bool $mollieOnboardingCompleted = false` within the  
   `Sylius\MolliePlugin\Entity\OnboardingStatusAwareTrait` which should be applied on the `AdminUser` entity.

1. Removed services:
   - `sylius_mollie_plugin.checker.version.mollie_plugin_latest_version_checker`

1. Removed Service IDs

The following service IDs have been renamed:

| Old Service ID                                                                                            | Use Instead                                                                   |
|-----------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| `sylius_mollie_plugin.checker.apple_pay.apple_pay_enabled_checker`                                        | `sylius_mollie.apple_pay.checker.apple_pay_enabled`                           |
| `sylius_mollie_plugin.applicator.units_promotion_adjustments_applicator`                                  | `sylius_mollie.voucher.applicator.units_promotion_adjustments`                |
| `sylius_mollie_plugin.calculator.calculate`                                                               | `sylius_mollie.calculator.calculate_tax_amount`                               |
| `sylius_mollie_plugin.cli.send_abandoned_payment_link`                                                    | `sylius_mollie.console.command.send_abandoned_payment_link`                   |
| `sylius_mollie_plugin.cli.subscription.begin_processing`                                                  | `sylius_mollie.console.command.subscription.begin_processing`                 |
| `sylius_mollie_plugin.cli.subscription.process`                                                           | `sylius_mollie.console.command.subscription.process`                          |
| `sylius_mollie_plugin.context.admin.admin_user_context`                                                   | `sylius_mollie.context.admin_user`                                            |
| `sylius_mollie_plugin.controller.action.admin.refund`                                                     | `sylius_mollie.controller.admin.refund`                                       |
| `sylius_mollie_plugin.controller.action.admin.methods`                                                    | `sylius_mollie.controller.admin.methods`                                      |
| `sylius_mollie_plugin.controller.action.admin.delete_payment_method_image`                                | `sylius_mollie.controller.admin.delete_payment_method_image`                  |
| `sylius_mollie_plugin.controller.action.shop.payment_fee_calculate_action`                                | `sylius_mollie.controller.shop.payment_fee_calculate`                         |
| `sylius_mollie_plugin.sylius_mollie_plugin.controller.action.shop.payment_fee_calculate_action`           | `sylius_mollie.controller.shop.payment_fee_calculate`                         |
| `sylius_mollie_plugin.controller.action.shop.credit_card_translation_controller`                          | `sylius_mollie.controller.shop.credit_card_translation`                       |
| `sylius_mollie_plugin.controller.action.shop.qr_code_action`                                              | `sylius_mollie.controller.shop.qr_code`                                       |
| `sylius_mollie_plugin.controller.action.shop.payum_controller`                                            | `sylius_mollie.controller.shop.payum`                                         |
| `sylius_mollie_plugin.controller.action.shop.payment_webhook_controller`                                  | `sylius_mollie.controller.shop.payment_webhook`                               |
| `sylius_mollie_plugin.controller.action.shop.page_redirect_controller`                                    | `sylius_mollie.controller.shop.page_redirect`                                 |
| `sylius_mollie_plugin.controller.action.admin.generate_paymentlink_action`                                | `sylius_mollie.controller.admin.generate_payment_link`                        |
| `sylius_mollie_plugin.controller.action.shop.apple_pay_validation_action`                                 | `sylius_mollie.controller.shop.apple_pay_validation`                          |
| `sylius_mollie_plugin.controller.action.shop.order_controller`                                            | `sylius_mollie.controller.shop.order`                                         |
| `sylius_mollie_plugin.controller.action.admin.mollie_subscription_controller`                             | `sylius_mollie.controller.admin.mollie_subscription`                          |
| `sylius_mollie_plugin.controller.action.admin.test_api_keys_action`                                       | `sylius_mollie.controller.admin.test_api_keys`                                |
| `sylius_mollie_plugin.controller.action.admin.change_position_payment_method_action`                      | `sylius_mollie.controller.admin.change_position_payment_method`               |
| `sylius_mollie_plugin.controller.onboarding_wizard.status`                                                | `sylius_mollie.controller.admin.onboarding_wizard.status`                     |
| `sylius_mollie_plugin.controller.onboarding_wizard.completed`                                             | `sylius_mollie.controller.admin.onboarding_wizard.completed`                  |
| `sylius_mollie_plugin.action.create_payment`                                                              | `sylius_mollie.payum.action.create_payment`                                   |
| `sylius_mollie_plugin.action.create_order`                                                                | `sylius_mollie.payum.action.create_order`                                     |
| `sylius_mollie_plugin.action.mollie_payment`                                                              | `sylius_mollie.api.payment_configuration_provider.mollie`                     |
| `sylius_mollie_plugin.action.api.create_on_demand_payment_action`                                         | `sylius_mollie.payum.action.create_on_demand_payment`                         |
| `sylius_mollie_plugin.action.api.create_on_demand_subscription_action`                                    | `sylius_mollie.payum.action.subscription.create_on_demand_subscription`       |
| `sylius_mollie_plugin.action.create_internal_subscription`                                                | `sylius_mollie.payum.action.subscription.create_internal_subscription`        |
| `sylius_mollie_plugin.payum_action.refund`                                                                | `sylius_mollie.payum.action.refund.refund`                                    |
| `sylius_mollie_plugin.payum_action.refund_order`                                                          | `sylius_mollie.payum.action.refund.refund.refund_order`                       |
| `sylius_mollie_plugin.action.capture`                                                                     | `sylius_mollie.payum.action.capture`                                          |
| `sylius_mollie_plugin.action.notify`                                                                      | `sylius_mollie.payum.action.notify`                                           |
| `sylius_mollie_plugin.action.status`                                                                      | `sylius_mollie.payum.action.status`                                           |
| `sylius_mollie_plugin.payum_action.api.create_customer`                                                   | `sylius_mollie.payum.action.create_customer`                                  |
| `sylius_mollie_plugin.action.convert_mollie_payment`                                                      | `sylius_mollie.payum.action.convert_mollie_payment`                           |
| `sylius_mollie_plugin.action.convert_mollie_subscription_payment`                                         | `sylius_mollie.payum.action.subscription.convert_mollie_subscription_payment` |
| `sylius_mollie_plugin.payum_action.state_machine.status_recurring_subscription`                           | `sylius_mollie.payum.action.subscription.status_recurring_subscription`       |
| `bit_bag.sylius_mollie_plugin.action.state_machine.applicator.subscription_and_payment_id_applicator`     | `sylius_mollie.state_machine.applicator.subscription_and_payment_id`          |
| `bit_bag.sylius_mollie_plugin.action.state_machine.applicator.subscription_and_sylius_payment_applicator` | `sylius_mollie.state_machine.applicator.subscription_and_sylius_payment`      |
| `bit_bag.sylius_mollie_plugin.action.state_machine.transition.payment_state_machine_transition`           | `sylius_mollie.state_machine.transition.payment`                              |
| `bit_bag.sylius_mollie_plugin.action.state_machine.transition.processing_state_machine_transition`        | `sylius_mollie.state_machine.transition.subscription_processing`              |
| `bit_bag.sylius_mollie_plugin.action.state_machine.transition.state_machine_transition`                   | `sylius_mollie.state_machine.transition.subscription`                         |
| `sylius_mollie_plugin.payum_action.state_machine.cancel_recurring_subscription`                           | `sylius_mollie.payum.action.subscription.cancel_recurring_subscription`       |
| `sylius_mollie_plugin.payum_action.order_set_status`                                                      | `sylius_mollie.state_machine.order_set_status`                                |
| `bit_bag.sylius_mollie_plugin.creator.payment_refund_command_creator`                                     | `sylius_mollie.refund.creator.payment_refund_command`                         |
| `sylius_mollie_plugin.creator.order_refund_command_creator`                                               | `sylius_mollie.refund.creator.order_refund_command`                           |
| `sylius_mollie_plugin.creator.abandoned_payment_link_creator`                                             | `sylius_mollie.creator.abandoned_payment_link`                                |
| `bit_bag.sylius_mollie_plugin.creator.mollie_methods_creator`                                             | `sylius_mollie.creator.mollie_methods`                                        |
| `sylius_mollie_plugin.creator.api_keys_test_creator`                                                      | `sylius_mollie.creator.api_keys_test`                                         |
| `sylius_mollie_plugin.creator.change_position_payment_method_creator`                                     | `sylius_mollie.updater.mollie_payment_method_position`                        |
| `sylius_mollie_plugin.creator.onboarding_wizard.status_creator`                                           | `sylius_mollie.creator.onboarding_wizard.status`                              |
| `sylius_mollie_plugin.repository.credit_memo_repository`                                                  | `sylius_mollie.repository.credit_memo`                                        |
| `sylius_mollie_plugin.distributor.order.order_voucher_distributor`                                        | `sylius_mollie.voucher.applicator.order_vouchers`                             |
| `sylius_mollie_plugin.documentation.documentation_links`                                                  | `sylius_mollie.documentation.documentation_links`                             |
| `sylius_mollie_plugin.event_listener.shipment_ship_event_listener`                                        | `sylius_mollie.listener.shipment_ship`                                        |
| `sylius_mollie_plugin.event_listener.payment_method_logo_upload_listener`                                 | `sylius_mollie.listener.payment_method_logo_upload`                           |
| `sylius_mollie_plugin.event_listener.payment_partial_event_listener`                                      | `sylius_mollie.listener.payment_partial`                                      |
| `sylius_mollie_plugin.event_listener.product_variant_recurring_options_listener`                          | `sylius_mollie.listener.product_variant_recurring_options`                    |
| `sylius_mollie_plugin.event_listener.refund_payment_generated_auto_complete_listener`                     | `sylius_mollie.listener.refund_payment_generated_auto_complete`               |
| `sylius_mollie_plugin.event_listener.`                                                                    | `sylius_mollie.listener.checkout_order_colliding_products`                    |
| `sylius_mollie_plugin.custom_factory.mollie_gateway_config`                                               | `sylius_mollie.custom_factory.mollie_gateway_config`                          |
| `sylius_mollie_plugin.factory.mollie_logger_factory`                                                      | `sylius_mollie.custom_factory.mollie_logger`                                  |
| `sylius_mollie_plugin.factory.shipment_factory`                                                           | `sylius_mollie.partial_ship.factory.shipment`                                 |
| `bit_bag.sylius_mollie_plugin.factory.shipment_factory`                                                   | `sylius_mollie.partial_ship.factory.shipment`                                 |
| `sylius_mollie_plugin.factory.onboarding_wizard.status_factory`                                           | `sylius_mollie.custom_factory.onboarding_wizard.status`                       |
| `sylius_mollie_plugin.custom_factory.mollie_subscription`                                                 | `sylius_mollie.custom_factory.mollie_subscription`                            |
| `sylius_mollie_plugin.custom_factory.mollie_subscription_schedule`                                        | `sylius_mollie.custom_factory.mollie_subscription_schedule`                   |
| `sylius_mollie_plugin.factory.methods`                                                                    | `sylius_mollie.factory.methods`                                               |
| `sylius_mollie_plugin.factory.date_periods`                                                               | `sylius_mollie.factory.date_period`                                           |
| `sylius_mollie_plugin.factory.payment_details_factory`                                                    | `sylius_mollie.factory.payment_details`                                       |
| `bit_bag.sylius_mollie_plugin.factory.api_customer_factory`                                               | `sylius_mollie.payum.factory.create_customer`                                 |
| `sylius_mollie_plugin.form.type.mollie_gateway_configuration`                                             | `sylius_mollie.form.type.mollie_gateway_configuration`                        |
| `sylius_mollie_plugin.form.type.mollie_subscription_gateway_configuration`                                | `sylius_mollie.form.type.mollie_subscription_gateway_configuration`           |
| `sylius_mollie_plugin.form.type.direct_debit`                                                             | `sylius_mollie.form.type.direct_debit`                                        |
| `sylius_mollie_plugin.form.extension.complete_type`                                                       | `sylius_mollie.form.extension.type.complete`                                  |
| `sylius_mollie_plugin.form.extension.product_variant_recurring`                                           | `sylius_mollie.form.extension.type.product_variant_recurring`                 |
| `sylius_mollie_plugin.form.extension.payment_type`                                                        | `sylius_mollie.form.extension.type.payment`                                   |
| `sylius_mollie_plugin.form.type.mollie_payment`                                                           | `sylius_mollie.form.type.mollie_payment`                                      |
| `sylius_mollie_plugin.form.transformer.mollie_interval`                                                   | `sylius_mollie.form.type.data_transformer.mollie_interval`                    |
| `sylius_mollie_plugin.form.type.mollie_interval`                                                          | `sylius_mollie.form.type.mollie_interval`                                     |
| `sylius_mollie_plugin.form.extension.payment_method_type`                                                 | `sylius_mollie.form.extension.type.gateway_config`                            |
| `sylius_mollie_plugin.form.extension.mollie_gateway_config`                                               | `sylius_mollie.form.type.mollie_gateway_config`                               |
| `sylius_mollie_plugin.form.type.payment_surcharge_fee`                                                    | `sylius_mollie.form.type.payment_surcharge_fee`                               |
| `sylius_mollie_plugin.form.type.customize_method_image`                                                   | `sylius_mollie.form.type.customize_method_image`                              |
| `sylius_mollie_plugin.grid.filter.mollie_logger_level`                                                    | `sylius_mollie.grid.filter.mollie_logger_level`                               |
| `sylius_mollie_plugin.grid.filter.mollie_subscription_state`                                              | `sylius_mollie.grid.filter.mollie_subscription_state`                         |
| `sylius_mollie_plugin.form.type.partial_ship.partial_ship_type`                                           | `sylius_mollie.form.type.partial_ship.partial_ship`                           |
| `sylius_mollie_plugin.form.extension.product_type_extension`                                              | `sylius_mollie.form.extension.type.product_type`                              |
| `sylius_mollie_plugin.form.type.product_type_type`                                                        | `sylius_mollie.form.type.product_type`                                        |
| `sylius_mollie_plugin.form.type.translation.block_translation_type`                                       | `sylius_mollie.form.type.translation.block_translation`                       |
| `sylius_mollie_plugin.form.type.translation.payment_method_translation`                                   | `sylius_mollie.form.type.translation.payment_method_translation`              |
| `sylius_mollie_plugin.checker.gateway.mollie_gateway_factory_checker`                                     | `sylius_mollie.payum.checker.mollie_gateway_factory`                          |
| `sylius_mollie_plugin.factory.mollie_gateway_factory`                                                     | `sylius_mollie.payum.gateway_factory.mollie_gateway`                          |
| `sylius_mollie_plugin.factory.mollie_subscription_gateway_factory`                                        | `sylius_mollie.payum.gateway_factory.mollie_subscription_gateway`             |
| `sylius_mollie_plugin.gateway_factory.mollie`                                                             | `sylius_mollie.payum.gateway_factory.builder.mollie`                          |
| `sylius_mollie_plugin.gateway_factory.mollie_subscription`                                                | `sylius_mollie.payum.gateway_factory.builder.mollie_subscription`             |
| `sylius_mollie_plugin.generator.subscription_schedule_generator`                                          | `sylius_mollie.subscription.generator.subscription_schedule`                  |
| `sylius_mollie_plugin.guard.subscription`                                                                 | `sylius_mollie.subscription.guard.subscription`                               |
| `sylius_mollie_plugin.helper.int_to_string`                                                               | `sylius_mollie.converter.int_to_string`                                       |
| `sylius_mollie_plugin.payments.order_converter`                                                           | `sylius_mollie.converter.order`                                               |
| `sylius_mollie_plugin.helper.convert_price_to_amount`                                                     | `sylius_mollie.converter.price_to_amount`                                     |
| `sylius_mollie_plugin.helper.convert_refund_data`                                                         | `sylius_mollie.refund.converter.refund_data`                                  |
| `sylius_mollie_plugin.helper.payment_description`                                                         | `sylius_mollie.provider.payment_description`                                  |
| `sylius_mollie_plugin.menu.mollie_menu_listener`                                                          | `sylius_mollie.menu_listener.mollie`                                          |
| `sylius_mollie_plugin.menu.order_show_menu_listener`                                                      | `sylius_mollie.menu_listener.admin_order_show`                                |
| `sylius_mollie_plugin.menu.mollie_email_template_menu_listener`                                           | `sylius_mollie.menu_listener.mollie_email_template`                           |
| `sylius_mollie_plugin.menu.mollie_recurring_menu_listener`                                                | `sylius_mollie.menu_listener.mollie_recurring`                                |
| `sylius_mollie_plugin.payments.methods`                                                                   | `sylius_mollie.registry.payment_method`                                       |
| `bit_bag.sylius_mollie_plugin.payments.method_resolver.mollie_method_filter`                              | `sylius_mollie.filter.mollie_method`                                          |
| `sylius_mollie_plugin.mollie_api_client`                                                                  | `sylius_mollie.client.mollie_api`                                             |
| `sylius_mollie_plugin.get_http_request`                                                                   | `sylius_mollie.payum.get_http_request`                                        |
| `sylius_mollie_plugin.logger.mollie_logger_action`                                                        | `sylius_mollie.logger.mollie_logger_action`                                   |
| `sylius_mollie_plugin.order.order_payment_refund`                                                         | `sylius_mollie.refund.handler.order_payment_refund`                           |
| `sylius_mollie_plugin.order.order_item_cloner`                                                            | `sylius_mollie.cloner.order_item`                                             |
| `sylius_mollie_plugin.order.adjustment_cloner`                                                            | `sylius_mollie.cloner.adjustment`                                             |
| `sylius_mollie_plugin.order.shipment_cloner`                                                              | `sylius_mollie.cloner.shipment`                                               |
| `sylius_mollie_plugin.order.shipment_unit_cloner`                                                         | `sylius_mollie.order.shipment_unit_cloner`                                    |
| `sylius_mollie_plugin.order.order_cloner`                                                                 | `sylius_mollie.cloner.subscription_order`                                     |
| `sylius_mollie_plugin.parser.response.guzzle_negative_response_parser`                                    | `sylius_mollie.client.parser.api_exception`                                   |
| `sylius_mollie_plugin.partial_ship.create_partial_ship_from_mollie`                                       | `sylius_mollie.partial_ship.converter.create_partial_ship_from_mollie`        |
| `sylius_mollie_plugin.processor.payment_surcharge_processor`                                              | `sylius_mollie.processor.payment_surcharge`                                   |
| `sylius_mollie_plugin.payment_surcharge.types.fix_amount`                                                 | `sylius_mollie.calculator.payment_fee.fixed_amount`                           |
| `sylius_mollie_plugin.payment_surcharge.types.fix_amount_and_percentage`                                  | `sylius_mollie.calculator.payment_fee.fixed_amount_and_percentage`            |
| `sylius_mollie_plugin.payment_surcharge.types.percentage`                                                 | `sylius_mollie.calculator.payment_fee.percentage`                             |
| `sylius_mollie_plugin.payment_surcharge.calculate`                                                        | `sylius_mollie.calculator.payment_fee.composite`                              |
| `sylius_mollie_plugin.email_sender.payment_link_email_sender`                                             | `sylius_mollie.mailer.sender.payment_link`                                    |
| `sylius_mollie_plugin.uploader.payment_method_logo_uploader`                                              | `sylius_mollie.uploader.payment_method_logo`                                  |
| `sylius_mollie_plugin.payment_processing.cancel_recurring_subscription`                                   | `sylius_mollie.subscription.processor.cancel_recurring_subscription`          |
| `sylius_mollie_plugin.payment_processing.subscription_payment_processor`                                  | `sylius_mollie.subscription.processor.subscription_payment`                   |
| `sylius_mollie_plugin.payment_resolver.mollie_payment_method`                                             | `sylius_mollie.payment_methods_resolver.mollie_payment`                       |
| `sylius_mollie_plugin.preparer.payment_link_email_preparer`                                               | `sylius_mollie.mailer.manager.payment_link_email`                             |
| `sylius_mollie_plugin.provider.apple.apple_pay_direct_provider`                                           | `sylius_mollie.apple_pay.provider.apple_pay_direct`                           |
| `sylius_mollie_plugin.provider.order.order_payment_apple_pay_direct_provider`                             | `sylius_mollie.apple_pay.provider.order_payment_apple_pay_direct`             |
| `sylius_mollie_plugin.provider.divisor.divisor_provider`                                                  | `sylius_mollie.provider.divisor`                                              |
| `sylius_mollie_plugin.provider.apple.apple_pay_direct_payment_provider`                                   | `sylius_mollie.apple_pay.provider.apple_pay_direct_payment`                   |
| `bit_bag.sylius_mollie_plugin.form.extension.resolver_group_provider`                                     | `sylius_mollie.form.resolver.product_variant_validation_groups`               |
| `sylius_mollie_plugin.purifier.partial_ship.order_shipment_purifier`                                      | `sylius_mollie.partial_ship.purifier.order_shipment`                          |
| `sylius_mollie_plugin.purifier.partial_ship.order_mollie_partial_ship`                                    | `sylius_mollie.partial_ship.order_mollie_partial_ship`                        |
| `sylius_mollie_plugin.purifier.mollie_payment_method_purifier`                                            | `sylius_mollie.purifier.mollie_payment_method`                                |
| `sylius_mollie_plugin.checker.refund.duplicate_refund_the_same_amount_checker`                            | `sylius_mollie.refund.checker.duplicate_refund_the_same_amount`               |
| `sylius_mollie_plugin.checker.refund.mollie_order_refund_checker`                                         | `sylius_mollie.refund.checker.mollie_order_refund`                            |
| `bit_bag.sylius_mollie_plugin.refund.payment_refund`                                                      | `sylius_mollie.refund.payment`                                                |
| `bit_bag.sylius_mollie_plugin.refund.shipment_refund`                                                     | `sylius_mollie.refund.units.shipment_unit`                                    |
| `bit_bag.sylius_mollie_plugin.refund.units_item_refund`                                                   | `sylius_mollie.refund.units.payment_units_item`                               |
| `sylius_mollie_plugin.refund.units_item_order_refund`                                                     | `sylius_mollie.refund.units.units_item_order`                                 |
| `sylius_mollie_plugin.refund.units.units_shipment_order_refund`                                           | `sylius_mollie.refund.units.units_shipment_order`                             |
| `sylius_mollie_plugin.refund.generator.payment_new_unit_refund_generator`                                 | `sylius_mollie.refund_generator.payment_new_unit`                             |
| `sylius_mollie_plugin.refund.generator.payment_refunded_generator`                                        | `sylius_mollie.refund.generator.payment`                                      |
| `sylius_mollie_plugin.calculator.refund.payment_refund_calculator`                                        | `sylius_mollie.refund.calculator.payment`                                     |
| `sylius_mollie_plugin.remover.partial_ship.old_shipment_items_remover`                                    | `sylius_mollie.partial_ship.remover.old_shipment_items`                       |
| `sylius_mollie_plugin.resolver.payment_methods`                                                           | `sylius_mollie.resolver.payment_methods`                                      |
| `sylius_mollie_plugin.resolver.payment_methods_image`                                                     | `sylius_mollie.resolver.payment_methods_image`                                |
| `sylius_mollie_plugin.resolver.payment_config`                                                            | `sylius_mollie.resolver.payment_config`                                       |
| `sylius_mollie_plugin.resolver.partial_ship.from_sylius_to_mollie_lines_resolver`                         | `sylius_mollie.partial_ship.resolver.from_sylius_to_mollie_lines`             |
| `sylius_mollie_plugin.resolver.partial_ship.from_mollie_to_sylius_resolver`                               | `sylius_mollie.partial_ship.resolver.from_mollie_to_sylius`                   |
| `sylius_mollie_plugin.resolver.payment_locale_resolver`                                                   | `sylius_mollie.resolver.payment_locale`                                       |
| `sylius_mollie_plugin.resolver.mollie_countries_restriction_resolver`                                     | `sylius_mollie.resolver.mollie_countries_restriction`                         |
| `sylius_mollie_plugin.resolver.mollie_factory_name`                                                       | `sylius_mollie.resolver.mollie_factory_name`                                  |
| `sylius_mollie_plugin.resolver.meal_voucher_resolver`                                                     | `sylius_mollie.resolver.meal_voucher`                                         |
| `sylius_mollie_plugin.resolver.mollie_api_client_key_resolver`                                            | `sylius_mollie.resolver.mollie_api_client_key`                                |
| `sylius_mollie_plugin.resolver.address.address_resolver`                                                  | `sylius_mollie.apple_pay.resolver.address`                                    |
| `sylius_mollie_plugin.resolver.address.apple_pay_address_resolver`                                        | `sylius_mollie.apple_pay.resolver.address.apple_pay_address`                  |
| `sylius_mollie_plugin.resolver.apple_pay_direct.apple_pay_direct_api_order_payment_resolver`              | `sylius_mollie.apple_pay.resolver.apple_pay_direct_api_order_payment`         |
| `sylius_mollie_plugin.resolver.apple_pay_direct.apple_pay_direct_api_payment_resolver`                    | `sylius_mollie.apple_pay.resolver.apple_pay_direct_api_payment`               |
| `sylius_mollie_plugin.resolver.apple_pay_direct.apple_pay_direct_payment_type_resolver`                   | `sylius_mollie.apple_pay.resolver.apple_pay_direct_payment_type`              |
| `sylius_mollie_plugin.resolver.api_keys_test_resolver`                                                    | `sylius_mollie.resolver.api_keys_test`                                        |
| `sylius_mollie_plugin.resolver.order.payment_checkout_order_resolver`                                     | `sylius_mollie.resolver.order.payment_checkout_order`                         |
| `sylius_mollie_plugin.creator.mollie_methods_resolver`                                                    | `sylius_mollie.resolver.mollie_methods`                                       |
| `sylius_mollie_plugin.resolver.mollie_allowed_methods_resolver`                                           | `sylius_mollie.resolver.mollie_allowed_methods`                               |
| `sylius_mollie_plugin.resolver.onboarding_wizard.status_resolver`                                         | `sylius_mollie.resolver.onboarding_wizard.status`                             |
| `sylius_mollie_plugin.processor.subscription_payment_processor`                                           | `sylius_mollie.subscription.processor.subscription`                           |
| `sylius_mollie_plugin.processor.subscription_schedule_processor`                                          | `sylius_mollie.subscription.processor.subscription_schedule`                  |
| `sylius_mollie_plugin.twig.parser.content_parser`                                                         | `sylius_mollie.twig.parser.content`                                           |
| `sylius_mollie_plugin.twig.extension.mollie_plugin_latest_version`                                        | `sylius_mollie.twig.extension.mollie_plugin_latest_version`                   |
| `sylius_mollie_plugin.twig.extension.customer_credit_cards`                                               | `sylius_mollie.twig.extension.customer_credit_cards`                          |
| `sylius_mollie_plugin.twig.extension.apple_pay_direct_enabled`                                            | `sylius_mollie.twig.extension.apple_pay_direct_enabled`                       |
| `sylius_mollie_plugin.twig.extension.divisor_provider`                                                    | `sylius_mollie.twig.extension.divisor_provider`                               |
| `sylius_mollie_plugin.updater.order.order_voucher_adjustment_updater`                                     | `sylius_mollie.voucher.updater.order_voucher_adjustment`                      |
| `sylius_mollie_plugin.validator.constraits.payment_surcharge_type_validator`                              | `sylius_mollie.validator.payment_surcharge_type`                              |
| `sylius_mollie_plugin.validator.apple_pay_direct.apple_pay_address_validator`                             | `sylius_mollie.apple_pay.validator.apple_pay_address`                         |
| `sylius_mollie_plugin.validator.apple_pay_direct.payment_method_checkout_validator`                       | `sylius_mollie.validator.apple_pay_direct.payment_method_checkout`            |
| `sylius_mollie_plugin.validator.constraints.payment_method_mollie_channel_unique_validator`               | `sylius_mollie.validator.payment_method_mollie_channel_unique`                |
| `sylius_mollie_plugin.validator.refund.refund_units_command_validator`                                    | `sylius_mollie.refund.validator.refund_units_command`                         |
| `sylius_mollie_plugin.checker.version.mollie_plugin_latest_version_checker`                               | `sylius_mollie.checker.version.mollie_plugin_latest_version`                  |
| `sylius_mollie_plugin.checker.voucher.product_voucher_type_checker`                                       | `sylius_mollie.voucher.checker.product_voucher_type`                          |
| `sylius_mollie_plugin.page.shop.account.order.index`                                                      | `sylius_mollie.behat.page.shop.account.order.index`                           |
| `sylius_mollie_plugin.page.shop.checkout.complete`                                                        | `sylius_mollie.behat.page.shop.checkout.complete`                             |
| `sylius_mollie_plugin.page.admin.order_index`                                                             | `sylius_mollie.behat.page.admin.order_index`                                  |
| `sylius_mollie_plugin.page.admin.order_show`                                                              | `sylius_mollie.behat.page.admin.order_show`                                   |
| `sylius_mollie_plugin.page.admin.payment_method.create`                                                   | `sylius_mollie.behat.page.admin.payment_method.create`                        |
| `sylius_mollie_plugin.behat.context.setup.mollie`                                                         | `sylius_mollie.behat.context.setup.mollie`                                    |
| `sylius_mollie_plugin.context.setup.order`                                                                | `sylius_mollie.behat.context.setup.order`                                     |
| `sylius_mollie_plugin.context.setup.subscription`                                                         | `sylius_mollie.behat.context.setup.subscription`                              |
| `sylius_mollie_plugin.behat.context.setup.product`                                                        | `sylius_mollie.behat.context.setup.product`                                   |
| `sylius_mollie_plugin.behat.context.ui.admin.managing_payment_method_mollie`                              | `sylius_mollie.behat.context.ui.admin.managing_payment_method_mollie`         |
| `sylius_mollie_plugin.behat.context.ui.shop.checkout`                                                     | `sylius_mollie.behat.context.ui.shop.checkout`                                |
| `sylius_mollie_plugin.behat.context.ui.shop.product`                                                      | `sylius_mollie.behat.context.ui.shop.product`                                 |
| `sylius_mollie_plugin.behat.context.ui.admin.refund`                                                      | `sylius_mollie.behat.context.ui.admin.refund`                                 |
| `sylius_mollie_plugin.behat.context.ui.shop.account`                                                      | `sylius_mollie.behat.context.ui.shop.account`                                 |
| `sylius_mollie_plugin.behat.context.ui.admin.order`                                                       | `sylius_mollie.behat.context.ui.admin.order`                                  |
| `sylius_mollie_plugin.behat.context.ui.admin.managing_orders`                                             | `sylius_mollie.behat.context.ui.admin.managing_orders`                        |
| `sylius_mollie_plugin.command_bus`                                                                        | `sylius_mollie.command_bus`                                                   |

1. Autowiring support has been added.

1. Removed classes:
   - `Sylius\MolliePlugin\Action\CaptureActionInterface`
   - `Sylius\MolliePlugin\Action\StatusActionInterface`
   - `Sylius\MolliePlugin\Request\StateMachine\SetStatusOrder`
   - `Sylius\MolliePlugin\Controller\Action\Admin\MollieSubscriptionController`
   - `Sylius\MolliePlugin\Controller\Action\Shop\PaymentFeeCalculateActionInterface`
   - `Sylius\MolliePlugin\Resolver\Address\ApplePayAddressResolver`
   - `Sylius\MolliePlugin\Resolver\Address\ApplePayAddressResolverInterface`
   - `Sylius\MolliePlugin\Order\ShipmentUnitCloner`
   - `Sylius\MolliePlugin\Order\ShipmentUnitClonerInterface`

1. Namespace changes:

| From                                                                                                 | To                                                                                            |
|------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------|
| `Sylius\MolliePlugin\Action\ApiPlatform\MolliePayment`                                               | `Sylius\MolliePlugin\Api\MolliePaymentConfigurationProvider`                                  |
| `Sylius\MolliePlugin\Action\Api\BaseRefundAction`                                                    | `Sylius\MolliePlugin\Payum\Action\Refund\BaseRefundAction`                                    |
| `Sylius\MolliePlugin\Action\RefundAction`                                                            | `Sylius\MolliePlugin\Payum\Action\Refund\RefundAction`                                        |
| `Sylius\MolliePlugin\Action\RefundOrderAction`                                                       | `Sylius\MolliePlugin\Payum\Action\Refund\RefundOrderAction`                                   |
| `Sylius\MolliePlugin\Action\Api\CancelRecurringSubscriptionAction`                                   | `Sylius\MolliePlugin\Payum\Action\Subscription\CancelRecurringSubscriptionAction`             |
| `Sylius\MolliePlugin\Action\ConvertMollieSubscriptionPaymentAction`                                  | `Sylius\MolliePlugin\Payum\Action\Subscription\ConvertMollieSubscriptionPaymentAction`        |
| `Sylius\MolliePlugin\Action\Api\CreateInternalSubscriptionAction`                                    | `Sylius\MolliePlugin\Payum\Action\Subscription\CreateInternalSubscriptionAction`              |
| `Sylius\MolliePlugin\Action\Api\CreateOnDemandSubscriptionAction`                                    | `Sylius\MolliePlugin\Payum\Action\Subscription\CreateOnDemandSubscriptionAction`              |
| `Sylius\MolliePlugin\Action\StateMachine\CreateOnDemandSubscriptionAction`                           | `Sylius\MolliePlugin\Payum\Action\Subscription\StatusRecurringSubscriptionAction`             |
| `Sylius\MolliePlugin\Action\Api\BaseApiAwareAction`                                                  | `Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction`                                         |
| `Sylius\MolliePlugin\Action\CaptureAction`                                                           | `Sylius\MolliePlugin\Payum\Action\CaptureAction`                                              |
| `Sylius\MolliePlugin\Action\ConvertMolliePaymentAction`                                              | `Sylius\MolliePlugin\Payum\Action\ConvertMolliePaymentAction`                                 |
| `Sylius\MolliePlugin\Action\Api\CreateCustomerAction`                                                | `Sylius\MolliePlugin\Payum\Action\CreateCustomerAction`                                       |
| `Sylius\MolliePlugin\Action\Api\CreateOnDemandPaymentAction`                                         | `Sylius\MolliePlugin\Payum\Action\CreateOnDemandPaymentAction`                                |
| `Sylius\MolliePlugin\Action\Api\CreateOrderAction`                                                   | `Sylius\MolliePlugin\Payum\Action\CreateOrderAction`                                          |
| `Sylius\MolliePlugin\Action\Api\CreatePaymentAction`                                                 | `Sylius\MolliePlugin\Payum\Action\CreatePaymentAction`                                        |
| `Sylius\MolliePlugin\Action\NotifyAction`                                                            | `Sylius\MolliePlugin\Payum\Action\NotifyAction`                                               |
| `Sylius\MolliePlugin\Action\StatusAction`                                                            | `Sylius\MolliePlugin\Payum\Action\StatusAction`                                               |
| `Sylius\MolliePlugin\Provider\PaymentToken\PaymentTokenProvider`                                     | `Sylius\MolliePlugin\Payum\Provider\PaymentTokenProvider`                                     |
| `Sylius\MolliePlugin\Provider\PaymentToken\PaymentTokenProviderInterface`                            | `Sylius\MolliePlugin\Payum\Provider\PaymentTokenProviderInterface`                            |
| `Sylius\MolliePlugin\Request\Api\RefundOrder`                                                        | `Sylius\MolliePlugin\Payum\Request\Refund\RefundOrder`                                        |
| `Sylius\MolliePlugin\Request\Api\CancelRecurringSubscription`                                        | `Sylius\MolliePlugin\Payum\Request\Seubcription\CancelRecurringSubscription`                  |
| `Sylius\MolliePlugin\Request\Api\CreateInternalRecurring`                                            | `Sylius\MolliePlugin\Payum\Request\Subscription\CreateInternalRecurring`                      |
| `Sylius\MolliePlugin\Request\StateMachine\CreateOnDemandSubscription`                                | `Sylius\MolliePlugin\Payum\Request\Subscription\CreateOnDemandSubscription`                   |
| `Sylius\MolliePlugin\Request\Api\CreateOnDemandSubscriptionPayment`                                  | `Sylius\MolliePlugin\Payum\Request\Subscription\CreateOnDemandSubscriptionPayment`            |
| `Sylius\MolliePlugin\Request\Api\CreateRecurringPayment`                                             | `Sylius\MolliePlugin\Payum\Request\Subscription\CreateRecurringPayment`                       |
| `Sylius\MolliePlugin\Request\Api\CreateSubscriptionPayment`                                          | `Sylius\MolliePlugin\Payum\Request\Subscription\CreateSubscriptionPayment`                    |
| `Sylius\MolliePlugin\Request\StateMachine\StatusRecurringSubscription`                               | `Sylius\MolliePlugin\Payum\Request\Subscription\StatusRecurringSubscription`                  |
| `Sylius\MolliePlugin\Request\Api\ConfigureNextOrder`                                                 | `Sylius\MolliePlugin\Payum\Request\ConfigureNextOrder`                                        |
| `Sylius\MolliePlugin\Request\Api\CreateCustomer`                                                     | `Sylius\MolliePlugin\Payum\Request\CreateCustomer`                                            |
| `Sylius\MolliePlugin\Request\Api\CreateOrder`                                                        | `Sylius\MolliePlugin\Payum\Request\CreateOrder`                                               |
| `Sylius\MolliePlugin\Request\Api\CreatePayment`                                                      | `Sylius\MolliePlugin\Payum\Request\CreatePayment`                                             |
| `Sylius\MolliePlugin\Request\Api\CreateSepaMandate`                                                  | `Sylius\MolliePlugin\Payum\Request\CreateSepaMandate`                                         |
| `Sylius\MolliePlugin\Request\Api\GetMethods`                                                         | `Sylius\MolliePlugin\Payum\Request\GetMethods`                                                |
| `Sylius\MolliePlugin\Action\StateMachine\SetStatusOrderAction`                                       | `Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicator`                     |
| `Sylius\MolliePlugin\Action\StateMachine\SetStatusOrderActionInterface`                              | `Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicatorInterface`            |
| `Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator`              | `Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator`              |
| `Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface`     | `Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface`     |
| `Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator`          | `Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicator`          |
| `Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface` | `Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface` |
| `Sylius\MolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransition`                   | `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition`                   |
| `Sylius\MolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface`          | `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransitionInterface`          |
| `Sylius\MolliePlugin\Action\StateMachine\Transition\StateMachineTransition`                          | `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransition`                          |
| `Sylius\MolliePlugin\Transition\MollieRecurringTransitions`                                          | `Sylius\MolliePlugin\StateMachine\MollieRecurringTransitions`                                 |
| `Sylius\MolliePlugin\Transition\MollieSubscriptionPaymentProcessingTransitions`                      | `Sylius\MolliePlugin\StateMachine\MollieSubscriptionPaymentProcessingTransitions`             |
| `Sylius\MolliePlugin\Transition\MollieSubscriptionProcessingTransitions`                             | `Sylius\MolliePlugin\StateMachine\MollieSubscriptionProcessingTransitions`                    |
| `Sylius\MolliePlugin\Transition\MollieSubscriptionTransitions`                                       | `Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions`                              |
| `Sylius\MolliePlugin\Transition\PartialShip\ShipmentTransitions`                                     | `Sylius\MolliePlugin\StateMachine\ShipmentTransitions`                                        |
| `Sylius\MolliePlugin\Calculator\Refund\PaymentRefundCalculator`                                      | `Sylius\MolliePlugin\Refund\Calculator\PaymentRefundCalculator`                               |
| `Sylius\MolliePlugin\Calculator\Refund\PaymentRefundCalculatorInterface`                             | `Sylius\MolliePlugin\Refund\Calculator\PaymentRefundCalculatorInterface`                      |
| `Sylius\MolliePlugin\Checker\Refund\DuplicateRefundTheSameAmountChecker`                             | `Sylius\MolliePlugin\Refund\Checker\DuplicateRefundTheSameAmountChecker`                      |
| `Sylius\MolliePlugin\Checker\Refund\DuplicateRefundTheSameAmountCheckerInterface`                    | `Sylius\MolliePlugin\Refund\Checker\DuplicateRefundTheSameAmountCheckerInterface`             |
| `Sylius\MolliePlugin\Checker\Refund\MollieOrderRefundChecker`                                        | `Sylius\MolliePlugin\Refund\Checker\MollieOrderRefundChecker`                                 |
| `Sylius\MolliePlugin\Checker\Refund\MollieOrderRefundCheckerInterface`                               | `Sylius\MolliePlugin\Refund\Checker\MollieOrderRefundCheckerInterface`                        |
| `Sylius\MolliePlugin\Validator\Refund\RefundUnitsCommandValidator`                                   | `Sylius\MolliePlugin\Refund\Validator\RefundUnitsCommandValidator`                            |
| `Sylius\MolliePlugin\PaymentFee\PaymentSurchargeFeeType`                                             | `Sylius\MolliePlugin\Model\PaymentSurchargeFeeType`                                           |
| `Sylius\MolliePlugin\PaymentFee\CompositePaymentSurchargeCalculator`                                 | `Sylius\MolliePlugin\Calculator\PaymentFee\CompositePaymentSurchargeCalculator`               |
| `Sylius\MolliePlugin\PaymentFee\Calculator\FixedAmountAndPercentageCalculator`                       | `Sylius\MolliePlugin\Calculator\PaymentFee\FixedAmountAndPercentageCalculator`                |
| `Sylius\MolliePlugin\PaymentFee\Calculator\FixedAmountCalculator`                                    | `Sylius\MolliePlugin\Calculator\PaymentFee\FixedAmountCalculator`                             |
| `Sylius\MolliePlugin\PaymentFee\Calculator\NoFeeCalculator`                                          | `Sylius\MolliePlugin\Calculator\PaymentFee\NoFeeCalculator`                                   |
| `Sylius\MolliePlugin\PaymentFee\Calculator\PaymentSurchargeCalculatorInterface`                      | `Sylius\MolliePlugin\Calculator\PaymentFee\PaymentSurchargeCalculatorInterface`               |
| `Sylius\MolliePlugin\PaymentFee\Calculator\PercentageCalculator`                                     | `Sylius\MolliePlugin\Calculator\PaymentFee\PercentageCalculator`                              |
| `Sylius\MolliePlugin\Cli\BeginProcessingSubscriptions`                                               | `Sylius\MolliePlugin\Console\Command\BeginProcessingSubscriptions`                            |
| `Sylius\MolliePlugin\Cli\ProcessSubscriptions`                                                       | `Sylius\MolliePlugin\Console\Command\ProcessSubscriptions`                                    |
| `Sylius\MolliePlugin\Cli\SendAbandonedPaymentLink`                                                   | `Sylius\MolliePlugin\Console\Command\SendAbandonedPaymentLink`                                |
| `Sylius\MolliePlugin\Context\Admin\AdminUserContext`                                                 | `Sylius\MolliePlugin\Context\AdminUserContext`                                                |
| `Sylius\MolliePlugin\Context\Admin\AdminUserContextInterface`                                        | `Sylius\MolliePlugin\Context\AdminUserContextInterface`                                       |
| `Sylius\MolliePlugin\Controller\Action\Admin\OnboardingWizard\CompleteAction`                        | `Sylius\MolliePlugin\Controller\Admin\OnboardingWizard\CompleteAction`                        |
| `Sylius\MolliePlugin\Controller\Action\Admin\OnboardingWizard\StatusAction`                          | `Sylius\MolliePlugin\Controller\Admin\OnboardingWizard\StatusAction`                          |
| `Sylius\MolliePlugin\Controller\Action\Admin\ChangePositionPaymentMethodAction`                      | `Sylius\MolliePlugin\Controller\Admin\ChangePositionPaymentMethodAction`                      |
| `Sylius\MolliePlugin\Controller\Action\Admin\DeletePaymentMethodImage`                               | `Sylius\MolliePlugin\Controller\Admin\DeletedPaymentMethodImageAction`                        |
| `Sylius\MolliePlugin\Controller\Action\Admin\GeneratePaymentlinkAction`                              | `Sylius\MolliePlugin\Controller\Admin\GeneratePaymentLinkAction`                              |
| `Sylius\MolliePlugin\Controller\Action\Admin\MethodsAction`                                          | `Sylius\MolliePlugin\Controller\Admin\MethodsAction`                                          |
| `Sylius\MolliePlugin\Controller\Action\Admin\Refund`                                                 | `Sylius\MolliePlugin\Controller\Admin\RefundAction`                                           |
| `Sylius\MolliePlugin\Controller\Action\Admin\TestApiKeysAction`                                      | `Sylius\MolliePlugin\Controller\Admin\TestApiKeysAction`                                      |
| `Sylius\MolliePlugin\Controller\Action\Shop\ApplePayValidationAction`                                | `Sylius\MolliePlugin\Controller\Shop\ApplePayValidationAction`                                |
| `Sylius\MolliePlugin\Controller\Action\Shop\CreditCardTranslationController`                         | `Sylius\MolliePlugin\Controller\Shop\CreditCardTranslationController`                         |
| `Sylius\MolliePlugin\Controller\Action\Shop\OrderController`                                         | `Sylius\MolliePlugin\Controller\Shop\OrderController`                                         |
| `Sylius\MolliePlugin\Controller\Action\Shop\PageRedirectController`                                  | `Sylius\MolliePlugin\Controller\Shop\PageRedirectController`                                  |
| `Sylius\MolliePlugin\Controller\Action\Shop\PaymentFeeCalculateAction`                               | `Sylius\MolliePlugin\Controller\Shop\PaymentFeeCalculateAction`                               |
| `Sylius\MolliePlugin\Controller\Action\Shop\PayumController`                                         | `Sylius\MolliePlugin\Controller\Shop\PayumController`                                         |
| `Sylius\MolliePlugin\Controller\Action\Shop\QrCodeAction`                                            | `Sylius\MolliePlugin\Controller\Shop\QrCodeAction`                                            |
| `Sylius\MolliePlugin\Applicator\Order\OrderVoucherApplicator`                                        | `Sylius\MolliePlugin\Voucher\Applicator\OrderVoucherApplicator`                               |
| `Sylius\MolliePlugin\Applicator\Order\OrderVoucherApplicatorInterface`                               | `Sylius\MolliePlugin\Voucher\Applicator\OrderVoucherApplicatorInterface`                      |
| `Sylius\MolliePlugin\Applicator\UnitsPromotionAdjustmentsApplicator`                                 | `Sylius\MolliePlugin\Voucher\Applicator\UnitsVouchersApplicator`                              |
| `Sylius\MolliePlugin\Applicator\UnitsPromotionAdjustmentsApplicatorInterface`                        | `Sylius\MolliePlugin\Voucher\Applicator\UnitsVouchersApplicatorInterface`                     |
| `Sylius\MolliePlugin\Checker\Voucher\ProductVoucherTypeChecker`                                      | `Sylius\MolliePlugin\Voucher\Checker\ProductVoucherTypeChecker`                               |
| `Sylius\MolliePlugin\Checker\Voucher\ProductVoucherTypeCheckerInterface`                             | `Sylius\MolliePlugin\Voucher\Checker\ProductVoucherTypeCheckerInterface`                      |
| `Sylius\MolliePlugin\Updater\Order\OrderVoucherAdjustmentUpdater`                                    | `Sylius\MolliePlugin\Voucher\Updater\OrderVoucherAdjustmentUpdater`                           |
| `Sylius\MolliePlugin\Updater\Order\OrderVoucherAdjustmentUpdaterInterface`                           | `Sylius\MolliePlugin\Voucher\Updater\OrderVoucherAdjustmentUpdaterInterface`                  |
| `Sylius\MolliePlugin\EmailSender\PaymentLinkEmailSender`                                             | `Sylius\MolliePlugin\Mailer\Sender\PaymentLinkEmailSender`                                    |
| `Sylius\MolliePlugin\EmailSender\PaymentLinkEmailSenderInterface`                                    | `Sylius\MolliePlugin\Mailer\Sender\PaymentLinkEmailSenderInterface`                           |
| `Sylius\MolliePlugin\Preparer\PaymentLinkEmailPreparer`                                              | `Sylius\MolliePlugin\Mailer\Manager\PaymentLinkEmailManager`                                  |
| `Sylius\MolliePlugin\Preparer\PaymentLinkEmailPreparerInterface`                                     | `Sylius\MolliePlugin\Mailer\Manager\PaymentLinkEmailPreparerInterface`                        |
| `Sylius\MolliePlugin\Provider\Form\ResolverGroupProvider`                                            | `Sylius\MolliePlugin\Form\Resolver\ProductVariantValidationGroupsResolver`                    |
| `Sylius\MolliePlugin\Provider\Form\ResolverGroupProviderInterface`                                   | `Sylius\MolliePlugin\Form\Resolver\ValidationGroupsResolverInterface`                         |
| `Sylius\MolliePlugin\Checker\Gateway\MollieGatewayFactoryChecker`                                    | `Sylius\MolliePlugin\Checker\Gateway\MollieGatewayFactoryChecker`                             |
| `Sylius\MolliePlugin\Checker\Gateway\MollieGatewayFactoryCheckerInterface`                           | `Sylius\MolliePlugin\Checker\Gateway\MollieGatewayFactoryCheckerInterface`                    |
| `Sylius\MolliePlugin\Factory\MollieGatewayFactory`                                                   | `Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory`                                      |
| `Sylius\MolliePlugin\Factory\MollieGatewayFactoryInterface`                                          | `Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactoryInterface`                             |
| `Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory`                                       | `Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory`                          |
| `Sylius\MolliePlugin\PaymentProcessing\CancelRecurringSubscriptionProcessor`                         | `Sylius\MolliePlugin\Subscription\Processor\CancelRecurringSubscriptionProcessor`             |
| `Sylius\MolliePlugin\PaymentProcessing\CancelRecurringSubscriptionProcessorInterface`                | `Sylius\MolliePlugin\Subscription\Processor\CancelRecurringSubscriptionProcessorInterface`    |
| `Sylius\MolliePlugin\PaymentProcessing\SubscriptionPaymentProcessor`                                 | `Sylius\MolliePlugin\Subscription\Processor\SubscriptionPaymentProcessor`                     |
| `Sylius\MolliePlugin\PaymentProcessing\SubscriptionPaymentProcessorInterface`                        | `Sylius\MolliePlugin\Subscription\Processor\SubscriptionPaymentProcessorInterface`            |
| `Sylius\MolliePlugin\Processor\SubscriptionProcessor`                                                | `Sylius\MolliePlugin\Subscription/Processor\SubscriptionProcessor`                            |
| `Sylius\MolliePlugin\Processor\SubscriptionProcessorInterface`                                       | `Sylius\MolliePlugin\Subscription/Processor\SubscriptionProcessorInterface`                   |
| `Sylius\MolliePlugin\Processor\SubscriptionScheduleProcessor`                                        | `Sylius\MolliePlugin\Subscription/Processor\SubscriptionScheduleProcessor`                    |
| `Sylius\MolliePlugin\Processor\SubscriptionScheduleProcessorInterface`                               | `Sylius\MolliePlugin\Subscription/Processor\SubscriptionScheduleProcessorInterface`           |
| `Sylius\MolliePlugin\Generator\SubscriptionScheduleGenerator`                                        | `Sylius\MolliePlugin\Subscription\Generator\SubscriptionScheduleGenerator`                    |
| `Sylius\MolliePlugin\Generator\SubscriptionScheduleGeneratorInterface`                               | `Sylius\MolliePlugin\Subscription\Generator\SubscriptionScheduleGeneratorInterface`           |
| `Sylius\MolliePlugin\Guard\SubscriptionGuard`                                                        | `Sylius\MolliePlugin\Subscription\Guard\SubscriptionGuard`                                    |
| `Sylius\MolliePlugin\Guard\SubscriptionGuardInterface`                                               | `Sylius\MolliePlugin\Subscription\Guard\SubscriptionGuardInterface`                           |
| `Sylius\MolliePlugin\PartialShip\CreatePartialShipFromMollie`                                        | `Sylius\MolliePlugin\PartialShip\Converter\CreatePartialShipFromMollie`                       |
| `Sylius\MolliePlugin\PartialShip\CreatePartialShipFromMollieInterface`                               | `Sylius\MolliePlugin\PartialShip\Converter\CreatePartialShipFromMollieInterface`              |
| `Sylius\MolliePlugin\Factory\PartialShip\ShipmentFactory`                                            | `Sylius\MolliePlugin\PartialShip\Factory\ShipmentFactory`                                     |
| `Sylius\MolliePlugin\Factory\PartialShip\ShipmentFactoryInterface`                                   | `Sylius\MolliePlugin\PartialShip\Factory\ShipmentFactoryInterface`                            |
| `Sylius\MolliePlugin\Purifier\PartialShip\OrderMolliePartialShip`                                    | `Sylius\MolliePlugin\PartialShip\OrderMolliePartialShip`                                      |
| `Sylius\MolliePlugin\Purifier\PartialShip\OrderMolliePartialShipInterface`                           | `Sylius\MolliePlugin\PartialShip\OrderMolliePartialShipInterface`                             |
| `Sylius\MolliePlugin\Purifier\PartialShip\OrderShipmentPurifier`                                     | `Sylius\MolliePlugin\PartialShip\Purifier\OrderShipmentPurifier`                              |
| `Sylius\MolliePlugin\Purifier\PartialShip\OrderShipmentPurifierInterface`                            | `Sylius\MolliePlugin\PartialShip\Purifier\OrderShipmentPurifierInterface`                     |
| `Sylius\MolliePlugin\Remover\PartialShip\OldShipmentItemsRemover`                                    | `Sylius\MolliePlugin\PartialShip\Remover\OldShipmentItemsRemover`                             |
| `Sylius\MolliePlugin\Remover\PartialShip\OldShipmentItemsRemoverInterface`                           | `Sylius\MolliePlugin\PartialShip\Remover\OldShipmentItemsRemoverInterface`                    |
| `Sylius\MolliePlugin\Resolver\PartialShip\FromMollieToSyliusResolver`                                | `Sylius\MolliePlugin\PartialShip\Resolver\FromMollieToSyliusResolver`                         |
| `Sylius\MolliePlugin\Resolver\PartialShip\FromMollieToSyliusResolverInterface`                       | `Sylius\MolliePlugin\PartialShip\Resolver\FromMollieToSyliusResolverInterface`                |
| `Sylius\MolliePlugin\Resolver\PartialShip\FromSyliusToMollieLinesResolver`                           | `Sylius\MolliePlugin\PartialShip\Resolver\FromSyliusToMollieLinesResolver`                    |
| `Sylius\MolliePlugin\Resolver\PartialShip\FromSyliusToMollieLinesResolverInterface`                  | `Sylius\MolliePlugin\PartialShip\Resolver\FromSyliusToMollieLinesResolverInterface`           |
| `Sylius\MolliePlugin\Factory\ApiCustomerFactory`                                                     | `Sylius\MolliePlugin\Payum\Factory\CreateCustomerFactory`                                     |
| `Sylius\MolliePlugin\Factory\ApiCustomerFactoryInterface`                                            | `Sylius\MolliePlugin\Payum\Factory\CreateCustomerFactoryInterface`                            |
| `Sylius\MolliePlugin\Checker\ApplePay\ApplePayEnabledChecker`                                        | `Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledChecker`                                 |
| `Sylius\MolliePlugin\Checker\ApplePay\ApplePayEnabledCheckerInterface`                               | `Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledCheckerInterface`                        |
| `Sylius\MolliePlugin\Provider\Apple\ApplePayDirectPaymentProvider`                                   | `Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectPaymentProvider`                         |
| `Sylius\MolliePlugin\Provider\Apple\ApplePayDirectPaymentProviderInterface`                          | `Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectPaymentProviderInterface`                |
| `Sylius\MolliePlugin\Provider\Apple\ApplePayDirectProvider`                                          | `Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectProvider`                                |
| `Sylius\MolliePlugin\Provider\Apple\ApplePayDirectProviderInterface`                                 | `Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectProviderInterface`                       |
| `Sylius\MolliePlugin\Provider\Order\OrderPaymentApplePayDirectProvider`                              | `Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProvider`                    |
| `Sylius\MolliePlugin\Provider\Order\OrderPaymentApplePayDirectProviderInterface`                     | `Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProviderInterface`           |
| `Sylius\MolliePlugin\Resolver\Address\AddressResolver`                                               | `Sylius\MolliePlugin\ApplePay\Resolver\AddressResolver`                                       |
| `Sylius\MolliePlugin\Resolver\Address\AddressResolverInterface`                                      | `Sylius\MolliePlugin\ApplePay\Resolver\AddressResolverInterface`                              |
| `Sylius\MolliePlugin\Resolver\ApplePayDirect\ApplePayDirectApiOrderPaymentResolver`                  | `Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectApiOrderPaymentResolver`                 |
| `Sylius\MolliePlugin\Resolver\ApplePayDirect\ApplePayDirectApiOrderPaymentResolverInterface`         | `Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectApiOrderPaymentResolverInterface`        |
| `Sylius\MolliePlugin\Resolver\ApplePayDirect\ApplePayDirectApiPaymentResolver`                       | `Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectApiPaymentResolver`                      |
| `Sylius\MolliePlugin\Resolver\ApplePayDirect\ApplePayDirectApiPaymentResolverInterface`              | `Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectApiPaymentResolverInterface`             |
| `Sylius\MolliePlugin\Resolver\ApplePayDirect\ApplePayDirectPaymentTypeResolver`                      | `Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectPaymentTypeResolver`                     |
| `Sylius\MolliePlugin\Resolver\ApplePayDirect\ApplePayDirectPaymentTypeResolverInterface`             | `Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectPaymentTypeResolverInterface`            |
| `Sylius\MolliePlugin\Validator\ApplePayDirect\ApplePayAddressValidator`                              | `Sylius\MolliePlugin\ApplePay\Validator\ApplePayAddressValidator`                             |
| `Sylius\MolliePlugin\Validator\ApplePayDirect\ApplePayAddressValidatorInterface`                     | `Sylius\MolliePlugin\ApplePay\Validator\ApplePayAddressValidatorInterface`                    |
| `Sylius\MolliePlugin\Order\OrderPaymentRefund`                                                       | `Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefund`                                       |
| `Sylius\MolliePlugin\Order\OrderPaymentRefundInterface`                                              | `Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefundInterface`                              |
| `Sylius\MolliePlugin\Order\AdjustmentInterface`                                                      | `Sylius\MolliePlugin\Model\AdjustmentInterface`                                               |
| `Sylius\MolliePlugin\Order\AdjustmentCloner`                                                         | `Sylius\MolliePlugin\Cloner\AdjustmentCloner`                                                 |
| `Sylius\MolliePlugin\Order\AdjustmentClonerInterface`                                                | `Sylius\MolliePlugin\Cloner\AdjustmentClonerInterface`                                        |
| `Sylius\MolliePlugin\Order\OrderItemCloner`                                                          | `Sylius\MolliePlugin\Cloner\OrderItemCloner`                                                  |
| `Sylius\MolliePlugin\Order\OrderItemClonerInterface`                                                 | `Sylius\MolliePlugin\Cloner\OrderItemClonerInterface`                                         |
| `Sylius\MolliePlugin\Order\ShipmentCloner`                                                           | `Sylius\MolliePlugin\Cloner\ShipmentCloner`                                                   |
| `Sylius\MolliePlugin\Order\ShipmentClonerInterface`                                                  | `Sylius\MolliePlugin\Cloner\ShipmentClonerInterface`                                          |
| `Sylius\MolliePlugin\Order\SubscriptionOrderCloner`                                                  | `Sylius\MolliePlugin\Cloner\SubscriptionOrderCloner`                                          |
| `Sylius\MolliePlugin\Order\SubscriptionOrderClonerInterface`                                         | `Sylius\MolliePlugin\Cloner\SubscriptionOrderClonerInterface`                                 |
| `Sylius\MolliePlugin\Creator\OrderRefundCommandCreator`                                              | `Sylius\MolliePlugin\Refund\Creator\OrderRefundCommandCreator`                                |
| `Sylius\MolliePlugin\Creator\OrderRefundCommandCreatorInterface`                                     | `Sylius\MolliePlugin\Refund\Creator\OrderRefundCommandCreatorInterface`                       |
| `Sylius\MolliePlugin\Creator\PaymentRefundCommandCreator`                                            | `Sylius\MolliePlugin\Refund\Creator\PaymentRefundCommandCreator`                              |
| `Sylius\MolliePlugin\Creator\PaymentRefundCommandCreatorInterface`                                   | `Sylius\MolliePlugin\Refund\Creator\PaymentRefundCommandCreatorInterface`                     |
| `Sylius\MolliePlugin\Helper\PaymentDescription`                                                      | `Sylius\MolliePlugin\Provider\PaymentDescriptionProvider`                                     |
| `Sylius\MolliePlugin\Helper\PaymentDescriptionInterface`                                             | `Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface`                            |
| `Sylius\MolliePlugin\Provider\Customer\CustomerProvider`                                             | `Sylius\MolliePlugin\Provider\CustomerProvider`                                               |
| `Sylius\MolliePlugin\Provider\Customer\CustomerProviderInterface`                                    | `Sylius\MolliePlugin\Provider\CustomerProviderInterface`                                      |
| `Sylius\MolliePlugin\Provider\Divisor\DivisorProvider`                                               | `Sylius\MolliePlugin\Provider\DivisorProvider`                                                |
| `Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface`                                      | `Sylius\MolliePlugin\Provider\DivisorProviderInterface`                                       |
| `Sylius\MolliePlugin\Helper\IntToStringConverter`                                                    | `Sylius\MolliePlugin\Converter\IntToStringConverter`                                          |
| `Sylius\MolliePlugin\Helper\IntToStringConverterInterface`                                           | `Sylius\MolliePlugin\Converter\IntToStringConverterInterface`                                 |
| `Sylius\MolliePlugin\Helper\ConvertOrder`                                                            | `Sylius\MolliePlugin\Converter\OrderConverter`                                                |
| `Sylius\MolliePlugin\Helper\ConvertOrderInterface`                                                   | `Sylius\MolliePlugin\Converter\OrderConverterInterface`                                       |
| `Sylius\MolliePlugin\Helper\ConvertPriceToAmount`                                                    | `Sylius\MolliePlugin\Converter\PriceToAmountConverter`                                        |
| `Sylius\MolliePlugin\Helper\ConvertRefundData`                                                       | `Sylius\MolliePlugin\Refund\Converter\RefundDataConverter`                                    |
| `Sylius\MolliePlugin\Helper\ConvertRefundDataInterface`                                              | `Sylius\MolliePlugin\Refund\Converter\RefundDataConverterInterface`                           |
| `Sylius\MolliePlugin\Parser\Response\GuzzleNegativeResponseParser`                                   | `Sylius\MolliePlugin\Client\Parser\ApiExceptionParser`                                        |
| `Sylius\MolliePlugin\Parser\Response\GuzzleNegativeResponseParserInterface`                          | `Sylius\MolliePlugin\Client\Parser\ApiExceptionParserInterface`                               |
| `Sylius\MolliePlugin\Creator\ChangePositionPaymentMethodCreator`                                     | `Sylius\MolliePlugin\Updater\MolliePaymentMethodPositionUpdater`                              |
| `Sylius\MolliePlugin\Creator\ChangePositionPaymentMethodCreatorInterface`                            | `Sylius\MolliePlugin\Updater\MolliePaymentMethodPositionUpdaterInterface`                     |
| `Sylius\MolliePlugin\DTO\ApiKeyTest`                                                                 | `Sylius\MolliePlugin\Model\DTO\ApiKeyTest`                                                    |
| `Sylius\MolliePlugin\DTO\PartialRefundItem`                                                          | `Sylius\MolliePlugin\Model\DTO\PartialRefundItem`                                             |
| `Sylius\MolliePlugin\DTO\PartialRefundItems`                                                         | `Sylius\MolliePlugin\Model\DTO\PartialRefundItems`                                            |
| `Sylius\MolliePlugin\DTO\PartialShipItem`                                                            | `Sylius\MolliePlugin\Model\DTO\PartialShipItem`                                               |
| `Sylius\MolliePlugin\DTO\PartialShipItems`                                                           | `Sylius\MolliePlugin\Model\DTO\PartialShipItems`                                              |
| `Sylius\MolliePlugin\DTO\MolliePayment\Amount`                                                       | `Sylius\MolliePlugin\Model\DTO\MolliePayment\Amount`                                          |
| `Sylius\MolliePlugin\DTO\MolliePayment\Metadata`                                                     | `Sylius\MolliePlugin\Model\DTO\MolliePayment\Metadata`                                        |
| `Sylius\MolliePlugin\DTO\MolliePayment\MolliePayment`                                                | `Sylius\MolliePlugin\Model\DTO\MolliePayment\MolliePayment`                                   |
| `Sylius\MolliePlugin\Payments\Methods\AbstractMethod`                                                | `Sylius\MolliePlugin\Model\PaymentMethod\AbstractMethod`                                      |
| `Sylius\MolliePlugin\Payments\Methods\Alma`                                                          | `Sylius\MolliePlugin\Model\PaymentMethod\Alma`                                                |
| `Sylius\MolliePlugin\Payments\Methods\ApplePay`                                                      | `Sylius\MolliePlugin\Model\PaymentMethod\ApplePay`                                            |
| `Sylius\MolliePlugin\Payments\Methods\Bancomatpay`                                                   | `Sylius\MolliePlugin\Model\PaymentMethod\Bancomatpay`                                         |
| `Sylius\MolliePlugin\Payments\Methods\Bancontact`                                                    | `Sylius\MolliePlugin\Model\PaymentMethod\Bancontact`                                          |
| `Sylius\MolliePlugin\Payments\Methods\BankTransfer`                                                  | `Sylius\MolliePlugin\Model\PaymentMethod\BankTransfer`                                        |
| `Sylius\MolliePlugin\Payments\Methods\Belfius`                                                       | `Sylius\MolliePlugin\Model\PaymentMethod\Belfius`                                             |
| `Sylius\MolliePlugin\Payments\Methods\Billie`                                                        | `Sylius\MolliePlugin\Model\PaymentMethod\Billie`                                              |
| `Sylius\MolliePlugin\Payments\Methods\Blik`                                                          | `Sylius\MolliePlugin\Model\PaymentMethod\Blik`                                                |
| `Sylius\MolliePlugin\Payments\Methods\ConfigTrait`                                                   | `Sylius\MolliePlugin\Model\PaymentMethod\ConfigTrait`                                         |
| `Sylius\MolliePlugin\Payments\Methods\CreditCard`                                                    | `Sylius\MolliePlugin\Model\PaymentMethod\CreditCard`                                          |
| `Sylius\MolliePlugin\Payments\Methods\DirectDebit`                                                   | `Sylius\MolliePlugin\Model\PaymentMethod\DirectDebit`                                         |
| `Sylius\MolliePlugin\Payments\Methods\Eps`                                                           | `Sylius\MolliePlugin\Model\PaymentMethod\Eps`                                                 |
| `Sylius\MolliePlugin\Payments\Methods\GiftCard`                                                      | `Sylius\MolliePlugin\Model\PaymentMethod\GiftCard`                                            |
| `Sylius\MolliePlugin\Payments\Methods\Giropay`                                                       | `Sylius\MolliePlugin\Model\PaymentMethod\Giropay`                                             |
| `Sylius\MolliePlugin\Payments\Methods\Ideal`                                                         | `Sylius\MolliePlugin\Model\PaymentMethod\Ideal`                                               |
| `Sylius\MolliePlugin\Payments\Methods\In3`                                                           | `Sylius\MolliePlugin\Model\PaymentMethod\In3`                                                 |
| `Sylius\MolliePlugin\Payments\Methods\Kbc`                                                           | `Sylius\MolliePlugin\Model\PaymentMethod\Kbc`                                                 |
| `Sylius\MolliePlugin\Payments\Methods\KlarnaOne`                                                     | `Sylius\MolliePlugin\Model\PaymentMethod\KlarnaOne`                                           |
| `Sylius\MolliePlugin\Payments\Methods\KlarnaPayNow`                                                  | `Sylius\MolliePlugin\Model\PaymentMethod\KlarnaPayNow`                                        |
| `Sylius\MolliePlugin\Payments\Methods\Klarnapaylater`                                                | `Sylius\MolliePlugin\Model\PaymentMethod\Klarnapaylater`                                      |
| `Sylius\MolliePlugin\Payments\Methods\Klarnasliceit`                                                 | `Sylius\MolliePlugin\Model\PaymentMethod\Klarnasliceit`                                       |
| `Sylius\MolliePlugin\Payments\Methods\MealVoucher`                                                   | `Sylius\MolliePlugin\Model\PaymentMethod\MealVoucher`                                         |
| `Sylius\MolliePlugin\Payments\Methods\MethodInterface`                                               | `Sylius\MolliePlugin\Model\PaymentMethod\MethodInterface`                                     |
| `Sylius\MolliePlugin\Payments\Methods\MyBank`                                                        | `Sylius\MolliePlugin\Model\PaymentMethod\MyBank`                                              |
| `Sylius\MolliePlugin\Payments\Methods\PayPal`                                                        | `Sylius\MolliePlugin\Model\PaymentMethod\PayPal`                                              |
| `Sylius\MolliePlugin\Payments\Methods\Payconiq`                                                      | `Sylius\MolliePlugin\Model\PaymentMethod\Payconiq`                                            |
| `Sylius\MolliePlugin\Payments\Methods\Przelewy24`                                                    | `Sylius\MolliePlugin\Model\PaymentMethod\Przelewy24`                                          |
| `Sylius\MolliePlugin\Payments\Methods\Riverty`                                                       | `Sylius\MolliePlugin\Model\PaymentMethod\Riverty`                                             |
| `Sylius\MolliePlugin\Payments\Methods\Satispay`                                                      | `Sylius\MolliePlugin\Model\PaymentMethod\Satispay`                                            |
| `Sylius\MolliePlugin\Payments\Methods\SofortBanking`                                                 | `Sylius\MolliePlugin\Model\PaymentMethod\SofortBanking`                                       |
| `Sylius\MolliePlugin\Payments\Methods\Trustly`                                                       | `Sylius\MolliePlugin\Model\PaymentMethod\Trustly`                                             |
| `Sylius\MolliePlugin\Payments\Methods\Twint`                                                         | `Sylius\MolliePlugin\Model\PaymentMethod\Twint`                                               |
| `Sylius\MolliePlugin\Payments\ApiTypeRestrictedPaymentMethods`                                       | `Sylius\MolliePlugin\Model\ApiTypeRestrictedPaymentMethods`                                   |
| `Sylius\MolliePlugin\Payments\PaymentType`                                                           | `Sylius\MolliePlugin\Model\ApiType`                                                           |
| `Sylius\MolliePlugin\Payments\Methods`                                                               | `Sylius\MolliePlugin\Registry\PaymentMethodRegistry`                                          |
| `Sylius\MolliePlugin\Payments\MethodsInterface`                                                      | `Sylius\MolliePlugin\Registry\PaymentMethodRegistryInterface`                                 |
| `Sylius\MolliePlugin\Payments\MethodResolver\MolliePaymentMethodResolver`                            | `Sylius\MolliePlugin\Resolver\PaymentMethodResolver`                                          |
| `Sylius\MolliePlugin\Payments\MethodResolver\MollieMethodFilter`                                     | `Sylius\MolliePlugin\Filter\MollieMethodFilter`                                               |
| `Sylius\MolliePlugin\Payments\MethodResolver\MollieMethodFilterInterface`                            | `Sylius\MolliePlugin\Filter\MollieMethodFilterInterface`                                      |

1. The `Sylius\MolliePlugin\Documentation\DocumentationLinks` class and the related service alias `sylius_mollie.documentation.documentation_links` have been removed.

Help links for Mollie configuration fields are now handled via a dedicated form theme:
`@SyliusMolliePlugin/Admin/PaymentMethod/_mollie_gateway_help_theme.html.twig.`

If you were previously injecting or using the `DocumentationLinks` service, please update your templates to rely on the form theme instead.

Make sure to register the theme in your template using:

- `{% form_theme form '@SyliusMolliePlugin/Admin/PaymentMethod/_mollie_gateway_help_theme.html.twig' %}`

1. Modified parameters:

The following parameters have been replaced:

| Removed parameter                                                                        | Use Instead                                                                       |
|------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------|
| `sylius_mollie_plugin.form.type.payment_methods.validation_groups.transport`             | `sylius_mollie.form.type.mollie_gateway_config.validation_groups`                 |
| `sylius_mollie_plugin.form.type.payment_methods.payment_surcharge_fee.validation_groups` | `sylius_mollie.form.type.payment_methods.payment_surcharge_fee.validation_groups` |
| `sylius_mollie_plugin.form.type.mollie.validation_groups`                                | `sylius_mollie.form.type.mollie.validation_groups`                                |
| `sylius_mollie_plugin.twig.functions`                                                    | `sylius_mollie.twig.functions`                                                    |
| `sylius_mollie_plugin_render_email_template`                                             | `sylius_mollie_render_email_template`                                             |

The following parameters have been removed:

- `sylius_mollie_plugin.admin.version.uri`
- `images_dir`

1. Renamed resources:

| From                                                | To                                           |
|-----------------------------------------------------|----------------------------------------------|
| `sylius_mollie_plugin.amount_limits`                | `sylius_mollie.amount_limits`                |
| `sylius_mollie_plugin.mollie_customer`              | `sylius_mollie.mollie_customer`              |
| `sylius_mollie_plugin.template_mollie_email`        | `sylius_mollie.template_mollie_email`        |
| `sylius_mollie_plugin.mollie_logger`                | `sylius_mollie.mollie_logger`                |
| `sylius_mollie_plugin.mollie_method_image`          | `sylius_mollie.mollie_method_image`          |
| `sylius_mollie_plugin.mollie_gateway_config`        | `sylius_mollie.mollie_gateway_config`        |
| `sylius_mollie_plugin.mollie_subscription`          | `sylius_mollie.mollie_subscription`          |
| `sylius_mollie_plugin.mollie_subscription_schedule` | `sylius_mollie.mollie_subscription_schedule` |
| `sylius_mollie_plugin.onboarding_wizard_status`     | `sylius_mollie.onboarding_wizard_status`     |
| `sylius_mollie_plugin.payment_surcharge_fee`        | `sylius_mollie.payment_surcharge_fee`        |
| `sylius_mollie_plugin.product_type`                 | `sylius_mollie.product_type`                 |

1. Doctrine migrations have been regenerated, meaning all previous migration files have been removed and their content
   is now in a single migration file. To apply the new migration and get rid of the old entries run migrations as usual:

   ```bash
       bin/console doctrine:migrations:migrate --no-interaction
   ```

1. Assets files have been reorganized and renamed, you need to update the following paths in your application:

    ```diff
    // assets/admin/entrypoint.js

    -    import '../../vendor/sylius/mollie-plugin/src/Resources/assets/admin/entry';
    +    import '../../vendor/sylius/mollie-plugin/assets/admin/entrypoint';
    ```

   and:

    ```diff
    // assets/shop/entrypoint.js

    -    import '../../vendor/sylius/mollie-plugin/src/Resources/assets/shop/entry';
    +    import '../../vendor/sylius/mollie-plugin/assets/shop/entrypoint';
    ```

1. The translation prefix has been changed from `sylius_mollie_plugin` to `sylius_mollie`.
