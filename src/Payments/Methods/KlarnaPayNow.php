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

namespace SyliusMolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class KlarnaPayNow extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::KLARNA_PAY_NOW;
    }

    public function getPaymentType(): string
    {
        return self::ORDER_API;
    }
}
