<?php

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Bancomatpay extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::BANCOMATPAY;
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
