<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Payments;

final class PaymentType
{
    public const ORDER_API = 'Orders API';

    public const ORDER_API_VALUE = 'ORDER_API';

    public const PAYMENT_API = 'Payments API';

    public const PAYMENT_API_VALUE = 'PAYMENT_API';

    public const SUBSCRIPTIONS_API = 'Subscriptions API';

    /** @return array<string, string> */
    public static function getAllAvailable() : array
    {
        return [
            self::PAYMENT_API => self::PAYMENT_API_VALUE,
            self::ORDER_API => self::ORDER_API_VALUE,
        ];
    }
}
