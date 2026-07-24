<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Calculator\CalculateTaxAmount;
use Sylius\MolliePlugin\Calculator\CalculateTaxAmountInterface;
use Sylius\MolliePlugin\Calculator\Clearer\PaymentFeeAdjustmentClearer;
use Sylius\MolliePlugin\Calculator\Clearer\PaymentFeeAdjustmentClearerInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\CompositePaymentSurchargeCalculator;
use Sylius\MolliePlugin\Calculator\PaymentFee\FixedAmountAndPercentageCalculator;
use Sylius\MolliePlugin\Calculator\PaymentFee\FixedAmountCalculator;
use Sylius\MolliePlugin\Calculator\PaymentFee\NoFeeCalculator;
use Sylius\MolliePlugin\Calculator\PaymentFee\PaymentSurchargeCalculatorInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\PercentageCalculator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.calculator.calculate_tax_amount', CalculateTaxAmount::class)
        ->public()
        ->args([service('sylius_mollie.converter.int_to_string')]);

    $services->alias(CalculateTaxAmountInterface::class, 'sylius_mollie.calculator.calculate_tax_amount');

    $services->set('sylius_mollie.calculator.payment_fee.fixed_amount', FixedAmountCalculator::class)
        ->args([
            service('sylius.factory.adjustment'),
            service('sylius_mollie.provider.divisor'),
        ])
        ->tag('sylius_mollie.payment_fee.calculator');

    $services->set('sylius_mollie.calculator.payment_fee.percentage', PercentageCalculator::class)
        ->args([
            service('sylius.factory.adjustment'),
            service('sylius_mollie.provider.divisor'),
        ])
        ->tag('sylius_mollie.payment_fee.calculator');

    $services->set('sylius_mollie.calculator.payment_fee.fixed_amount_and_percentage', FixedAmountAndPercentageCalculator::class)
        ->args([
            service('sylius.factory.adjustment'),
            service('sylius_mollie.calculator.payment_fee.percentage'),
            service('sylius_mollie.calculator.payment_fee.fixed_amount'),
            service('sylius_mollie.provider.divisor'),
        ])
        ->tag('sylius_mollie.payment_fee.calculator');

    $services->set('sylius_mollie.calculator.payment_fee.no_fee', NoFeeCalculator::class)
        ->tag('sylius_mollie.payment_fee.calculator');

    $services->set('sylius_mollie.calculator.payment_fee.composite', CompositePaymentSurchargeCalculator::class)
        ->args([tagged_iterator('sylius_mollie.payment_fee.calculator')]);

    $services->alias(PaymentSurchargeCalculatorInterface::class, 'sylius_mollie.calculator.payment_fee.composite');

    $services->set('sylius_mollie.calculator.clearer.payment_fee_adjustment', PaymentFeeAdjustmentClearer::class);

    $services->alias(PaymentFeeAdjustmentClearerInterface::class, 'sylius_mollie.calculator.clearer.payment_fee_adjustment');
};
