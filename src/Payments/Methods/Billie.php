<?php

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Billie extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::BILLIE;
    }

    public function getPaymentType(): string
    {
        return self::ORDER_API;
    }
}
