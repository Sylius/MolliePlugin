<?php

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Twint extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::TWINT;
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
