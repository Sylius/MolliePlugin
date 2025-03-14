<?php

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Alma extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::ALMA;
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
