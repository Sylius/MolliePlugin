<?php

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class KlarnaOne extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::KLARNA_ONE;
    }

    public function getPaymentType(): string
    {
        return self::ORDER_API;
    }
}
