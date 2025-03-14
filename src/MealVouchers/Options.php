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

namespace SyliusMolliePlugin\MealVouchers;

final class Options
{
    public const MEAL = 'meal';

    public const ECO = 'eco';

    public const GIFT = 'gift';

    public static function getAvailableMealVouchersCategory(): array
    {
        return [
            self::MEAL => self::MEAL,
            self::ECO => self::ECO,
            self::GIFT => self::GIFT,
        ];
    }
}
