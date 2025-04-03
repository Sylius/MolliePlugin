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

namespace Sylius\MolliePlugin\Model\PaymentMethod;

use Sylius\MolliePlugin\Model\ApiType;

final class MealVoucher extends AbstractMethod
{
    public const MEAL_VOUCHERS = 'voucher';

    public function getMethodId(): string
    {
        return self::MEAL_VOUCHERS;
    }

    public function getPaymentType(): string
    {
        return ApiType::ORDER_API_VALUE;
    }
}
