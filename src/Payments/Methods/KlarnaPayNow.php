<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class KlarnaPayNow extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::KLARNA_PAY_NOW;
    }

    public function getPaymentType(): string
    {
        return self::ORDER_API;
    }
}
