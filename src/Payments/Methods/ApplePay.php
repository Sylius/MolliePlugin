<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class ApplePay extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::APPLEPAY;
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
