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

use SyliusMolliePlugin\Payments\PaymentTerms\Options;
use Mollie\Api\Types\PaymentMethod;

final class MyBank extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::MYBANK;
    }

    public function getPaymentType(): string
    {
        return Options::ORDER_API;
    }
}
