@create_scheduled_mollie_subscription_payment
Feature: Create scheduled Mollie Subscription payment
  In order to buy subscription products
  As an User
  I want app to automatically create my recurring payments

  Background:
    Given the store operates on a single channel in "United States"
    And the store has a product "Angel T-Shirt"
    And the store ships everywhere for free
    And I am logged in as an administrator
    And the store has a payment method "Mollie Subscription" with a code "mollie_subscription" and Mollie Subscription payment gateway
    And I am a logged in customer
    And I placed an order "#00000666"
    And I bought a single "Angel T-Shirt"
    And I addressed it to "Lucifer Morningstar", "Seaside Fwy", "90802" "Los Angeles" in the "United States" with identical billing address
    And I chose "Free" shipping method with "Mollie Subscription" payment
    And this order is already paid
    And this order has an active mollie subscription
    And this subscription has an active schedule
    And I am logged in as an administrator

  @ui
  Scenario: Recurring payment
    When I go to "/"
    When I browse orders
    Then I should see a single order from customer "sylius@example.com"
    When I run command "cd tests/Application && APP_ENV=test php bin/console mollie:subscription:begin-processing"
    Then I should see "[OK] Successfully marked scheduled subscriptions" in the output
    When I run command "cd tests/Application && APP_ENV=test php bin/console mollie:subscription:process"
    Then I should see "[OK] Successfully marked scheduled subscriptions" in the output
    When I browse orders
    Then I should see 2 orders in the list
    When I browse payments
    Then I should see 2 payments in the list
    When I choose "Completed" as a payment state
#    TODO To check whether the payment status should change to “completed” after the subscription command succeeds.
#    And I filter
    Then I should see 2 payments in the list

  @ui
  Scenario: Recurring payment with changed price
    When I go to "/"
    When I browse orders
    Then I should see a single order from customer "sylius@example.com"
    When I view the summary of the order "#00000666"
    Then the order's total should be "$1.00"
    And it should be paid with "Mollie Subscription"
    Then I change ANGEL_T_SHIRT product variant price to "5.00"
    When I run command "cd tests/Application && APP_ENV=test php bin/console mollie:subscription:begin-processing"
    Then I should see "[OK] Successfully marked scheduled subscriptions" in the output
    When I run command "cd tests/Application && APP_ENV=test php bin/console mollie:subscription:process"
    Then I should see "[OK] Successfully marked scheduled subscriptions" in the output
    When I browse orders
    Then I should see 2 orders in the list
    And all orders have same total set to "$1.00"
    When I browse payments
    Then I should see 2 payments in the list
    When I choose "Completed" as a payment state
#    TODO To check whether the payment status should change to “completed” after the subscription command succeeds.
#    And I filter
    Then I should see 2 payments in the list

  @ui
  Scenario: Failed recurring payment
    When this order is incomplete
    And I go to "/"
    When I run command "cd tests/Application && APP_ENV=test php bin/console mollie:subscription:begin-processing"
    Then I should see "[OK] Successfully marked scheduled subscriptions" in the output
    When I run command "cd tests/Application && APP_ENV=test php bin/console mollie:subscription:process"
    Then I should see "[ERROR] An error has occurred during send payment link process." in the output
    When I browse orders
    Then I should see a single order from customer "sylius@example.com"
    When I browse payments
    Then I should see a single payment in the list
    When I choose "Completed" as a payment state
#    TODO To check whether the payment status should change to “completed” after the subscription command succeeds.
#    And I filter
    Then I should see a single payment in the list

