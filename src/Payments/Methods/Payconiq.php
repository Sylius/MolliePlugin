<?php

namespace Sylius\MolliePlugin\Payments\Methods;

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
