<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SyliusMolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Payconiq extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::PAYCONIQ;
    }
    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
