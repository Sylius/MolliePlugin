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

namespace Sylius\MolliePlugin\Payments\PaymentTerms;

use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;

final class Options
{
    public const LOG_NOTHING = 'sylius_mollie_plugin.ui.nothing_log';

    public const LOG_ERRORS = 'sylius_mollie_plugin.ui.errors';

    public const LOG_EVERYTHING = 'sylius_mollie_plugin.ui.everything';

    public const LOG_INFO = 'sylius_mollie_plugin.ui.info';

    public static function getDebugLevels(): array
    {
        return [
            self::LOG_NOTHING => MollieLoggerActionInterface::LOG_DISABLED,
            self::LOG_ERRORS => MollieLoggerActionInterface::LOG_ERRORS,
            self::LOG_EVERYTHING => MollieLoggerActionInterface::LOG_EVERYTHING,
        ];
    }

    /** @return array<string, int> */
    public static function getLogLevels(): array
    {
        return [
            self::LOG_INFO => MollieLoggerActionInterface::LOG_ERRORS,
            self::LOG_ERRORS => MollieLoggerActionInterface::LOG_EVERYTHING,
        ];
    }
}
