<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\MolliePlugin\Model;

final class PaymentSurchargeFeeType
{
    public const PERCENTAGE = 'percentage';

    public const FIXED = 'fixed_fee';

    public const FIXED_AND_PERCENTAGE = 'fixed_fee_and_percentage';

    public const NONE = 'no_fee';

    /** @return array<string, string> */
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
