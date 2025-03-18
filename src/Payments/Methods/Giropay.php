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

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Giropay extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::GIROPAY;
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
