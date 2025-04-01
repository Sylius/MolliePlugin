@creating_order_through_admin
Feature: Creating order through admin with Mollie Subscription payment
    In order to place order in the name of a Customer and forward the payment to them
    As an Administrator
    I want to be able to create an order with Mollie Subscription payment in Admin panel

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "Stark Coat" priced at "$100.00"
        And the store has a product "Lannister Banner" priced at "$40.00"
        And the store ships everywhere for free
        And the store has a payment method "Mollie Subscription" with a code "mollie_subscription" and Mollie Subscription payment gateway
        And there is a customer account "mail@example.com"
        And I am logged in as an administrator

    @ui @javascript
    Scenario: Creating an order with Mollie Subscription payment for an existing customer
        When I create a new order for "mail@example.com" and channel "United States"
        And I add "Stark Coat" to this order
        And I specify this order shipping address as "Ankh Morpork", "Frost Alley", "90210", "United States" for "Jon Snow"
        And I select "Mollie Subscription" payment method
        And I place and confirm this order
        Then I should be notified that order has been successfully created
        And there should be one not paid nor shipped order with channel "United States" for "mail@example.com" in the registry
        When I view summary of last order
        Then it should be paid with "Mollie Subscription"
