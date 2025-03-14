<?php

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Blik extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::BLIK;
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
