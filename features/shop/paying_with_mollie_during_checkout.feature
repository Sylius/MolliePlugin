@paying_with_mollie_for_order
Feature: Paying with Mollie during checkout
    In order to buy products
    As a Customer
    I want to be able to pay with Mollie

    Background:
        Given the store operates on a single channel in "United States"
        And there is a user "john@mollie.pl" identified by "password123"
        And the store has a payment method "Mollie" with a code "mollie" and Mollie payment gateway
        And gateway "mollie" has all methods loaded and enabled
        And the store ships everywhere for free
        And I am logged in as "john@mollie.pl"

    @ui
    Scenario: Successful payment
        Given the store has a product "PHP T-Shirt" priced at "$19.99"
        Given I added product "PHP T-Shirt" to the cart
        And I have proceeded selecting "Mollie" payment method
        When I select "creditcard" as my payment option
        Then I press "Next"
        When I confirm my order
        Then I should be notified that my payment has been completed
        When I am logged in as an administrator
        And I browse orders
        Then I should see a single order from customer "john@mollie.pl"
        And I view summary of last order
        And it should be paid with "Mollie"

    @ui
    Scenario: Cancelling the payment
        Given the store has a product "PHP T-Shirt" priced at "$9.99"
        Given I added product "PHP T-Shirt" to the cart
        And I have proceeded selecting "Mollie" payment method
        When I select "creditcard" as my payment option
        Then I press "Next"
        When I confirm my order
        Then I should be notified that my payment has been cancelled
        And I should be able to pay again

    @ui
    Scenario: Retrying the payment with success
        Given the store has a product "PHP T-Shirt" priced at "$9.99"
        Given I added product "PHP T-Shirt" to the cart
        And I have proceeded selecting "Mollie" payment method
        When I select "creditcard" as my payment option
        Then I press "Next"
        When I confirm my order
        Then I should be notified that my payment has been cancelled
        And I should be able to pay again
        And I fix payment amount to pay successfully
        When I select "creditcard" as my payment option
        Then I press "Pay"
        Then I should be notified that my payment has been completed
        And I should see the thank you page

    @ui
    Scenario: Retrying the payment with failure
        Given the store has a product "PHP T-Shirt" priced at "$9.99"
        Given I added product "PHP T-Shirt" to the cart
        And I have proceeded selecting "Mollie" payment method
        When I select "creditcard" as my payment option
        Then I press "Next"
        When I confirm my order
        Then I should be notified that my payment has been cancelled
        And I should be able to pay again
        When I select "creditcard" as my payment option
        Then I press "Pay"
        Then I should be notified that my payment has been cancelled
        And I should be able to pay again

