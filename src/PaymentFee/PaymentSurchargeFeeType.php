<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\PaymentFee;

final class PaymentSurchargeFeeType
{
    public const PERCENTAGE = 'percentage';

    public const FIXED = 'fixed_fee';

    public const FIXED_AND_PERCENTAGE = 'fixed_fee_and_percentage';

    public const NONE = 'no_fee';

    public static function getAllAvailable(): array
    {
        return [
            self::NONE => self::NONE,
            self::PERCENTAGE => self::PERCENTAGE,
            self::FIXED => self::FIXED,
            self::FIXED_AND_PERCENTAGE => self::FIXED_AND_PERCENTAGE,
        ];
    }
}
