<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class Bancontact extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::BANCONTACT;
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
