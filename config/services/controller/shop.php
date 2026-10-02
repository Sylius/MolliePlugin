<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Controller\Shop\ApplePayValidationAction;
use Sylius\MolliePlugin\Controller\Shop\CreditCardTranslationController;
use Sylius\MolliePlugin\Controller\Shop\NoPaymentFeeCalculateAction;
use Sylius\MolliePlugin\Controller\Shop\OrderController;
use Sylius\MolliePlugin\Controller\Shop\PageRedirectController;
use Sylius\MolliePlugin\Controller\Shop\PaymentFeeCalculateAction;
use Sylius\MolliePlugin\Controller\Shop\PaymentWebhookController;
use Sylius\MolliePlugin\Controller\Shop\PayumController;
use Sylius\MolliePlugin\Controller\Shop\QrCodeAction;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.controller.shop.payment_fee_calculate', PaymentFeeCalculateAction::class)
        ->args([
            service('sylius_mollie.calculator.payment_fee.composite'),
            service('sylius.context.cart'),
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('sylius.aggregator.adjustments_by_label'),
            service('sylius_mollie.converter.price_to_amount'),
            service('twig'),
            service('sylius_mollie.calculator.clearer.payment_fee_adjustment'),
        ]);

    $services->set('sylius_mollie.controller.shop.no_payment_fee_calculate', NoPaymentFeeCalculateAction::class)
        ->args([
            service('sylius.context.cart'),
            service('sylius_mollie.converter.price_to_amount'),
            service('sylius_mollie.calculator.clearer.payment_fee_adjustment'),
        ]);

    $services->set('sylius_mollie.controller.shop.qr_code', QrCodeAction::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius.context.cart'),
            service('sylius_mollie.client.mollie_api'),
            service('sylius_mollie.resolver.mollie_api_client_key'),
            service('sylius.repository.order'),
            service('router'),
            service('sylius_mollie.repository.mollie_gateway_config'),
            service('sylius_mollie.converter.int_to_string'),
        ]);

    $services->set('sylius_mollie.controller.shop.credit_card_translation', CreditCardTranslationController::class)
        ->args([service('translator')]);

    $services->set('sylius_mollie.controller.shop.payum', PayumController::class)
        ->args([
            service('payum'),
            service('sylius.repository.order'),
            service('router'),
            service('sylius_abstraction.state_machine'),
            service('doctrine.orm.default_entity_manager'),
        ]);

    $services->set('sylius_mollie.controller.shop.payment_webhook', PaymentWebhookController::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius_mollie.resolver.mollie_api_client_key'),
            service('sylius.repository.order'),
            service('sylius.repository.payment'),
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_abstraction.state_machine'),
            service('doctrine.orm.default_entity_manager'),
        ]);

    $services->set('sylius_mollie.controller.shop.page_redirect', PageRedirectController::class)
        ->args([
            service('router'),
            service('sylius.repository.order'),
        ]);

    $services->set('sylius_mollie.controller.shop.apple_pay_validation', ApplePayValidationAction::class)
        ->args([
            service('sylius_mollie.logger.mollie_logger_action'),
            service('sylius_mollie.resolver.mollie_api_client_key'),
            service('sylius_mollie.apple_pay.checker.apple_pay_enabled'),
        ]);

    $services->set('sylius_mollie.controller.shop.order', OrderController::class)
        ->parent('sylius.controller.order');
};
