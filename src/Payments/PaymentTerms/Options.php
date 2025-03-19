<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\MolliePlugin\Payments\PaymentTerms;

use Mollie\Api\Types\PaymentMethod;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payments\Methods\MealVoucher;

final class Options
{
    public const ORDER_API = 'Orders API';

    public const ORDER_API_VALUE = 'ORDER_API';

    public const PAYMENT_API = 'Payments API';

    public const PAYMENT_API_VALUE = 'PAYMENT_API';

    public const SUBSCRIPTIONS_API = 'Subscriptions API';

    public const PERCENTAGE = 'percentage';

    public const FIXED_FEE = 'fixed_fee';

    public const FIXED_FEE_AND_PERCENTAGE = 'fixed_fee_and_percentage';

    public const LOG_NOTHING = 'sylius_mollie_plugin.ui.nothing_log';

    public const LOG_ERRORS = 'sylius_mollie_plugin.ui.errors';

    public const LOG_EVERYTHING = 'sylius_mollie_plugin.ui.everything';

    public const LOG_INFO = 'sylius_mollie_plugin.ui.info';

    public static function getAvailablePaymentType(): array
    {
        return [
            self::PAYMENT_API => self::PAYMENT_API_VALUE,
            self::ORDER_API => self::ORDER_API_VALUE,
        ];
    }

    public static function getAvailablePaymentSurchargeFeeType(): array
    {
        return [
            self::PERCENTAGE => self::PERCENTAGE,
            self::FIXED_FEE => self::FIXED_FEE,
            self::FIXED_FEE_AND_PERCENTAGE => self::FIXED_FEE_AND_PERCENTAGE,
        ];
    }

    public static function getDebugLevels(): array
    {
        return [
            self::LOG_NOTHING => MollieLoggerActionInterface::LOG_DISABLED,
            self::LOG_ERRORS => MollieLoggerActionInterface::LOG_ERRORS,
            self::LOG_EVERYTHING => MollieLoggerActionInterface::LOG_EVERYTHING,
        ];
    }

    public static function getLogLevels(): array
    {
        return [
            self::LOG_INFO => MollieLoggerActionInterface::LOG_ERRORS,
            self::LOG_ERRORS => MollieLoggerActionInterface::LOG_EVERYTHING,
        ];
    }

    public static function getOnlyOrderAPIMethods(): array
    {
        return [
            PaymentMethod::KLARNA_ONE,
            PaymentMethod::KLARNA_PAY_NOW,
            PaymentMethod::KLARNA_PAY_LATER,
            PaymentMethod::KLARNA_SLICE_IT,
            PaymentMethod::IN3,
            PaymentMethod::BILLIE,
            MealVoucher::MEAL_VOUCHERS,
            PaymentMethod::RIVERTY,
        ];
    }

    public static function getOnlyPaymentAPIMethods(): array
    {
        return [
            PaymentMethod::ALMA,
        ];
    }
}
