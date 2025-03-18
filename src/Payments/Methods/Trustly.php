<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\MolliePlugin\Payments\Methods;


class Trustly extends AbstractMethod
{
    public function getMethodId(): string
    {
        return 'trustly';
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
