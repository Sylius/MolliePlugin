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

namespace Sylius\MolliePlugin\Model;

use Mollie\Api\Types\PaymentMethod;
use Sylius\MolliePlugin\Model\PaymentMethod\MealVoucher;

final class ApiTypeRestrictedPaymentMethods
{
    /** @return array<array-key, string> */
    public static function onlyOrderApi(): array
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

    /** @return array<array-key, string> */
    public static function onlyPaymentApi(): array
    {
        return [
            PaymentMethod::ALMA,
        ];
    }
}
