<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;

final class GiftCard extends AbstractMethod
{
    public function getMethodId(): string
    {
        return PaymentMethod::GIFTCARD;
    }

    public function isCanRefunded(): bool
    {
        return false;
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
