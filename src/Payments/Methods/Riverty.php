<?php

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Riverty extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::RIVERTY;
    }
    public function getPaymentType(): string
    {
        return self::ORDER_API;
    }
}
