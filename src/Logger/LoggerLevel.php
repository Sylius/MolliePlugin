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

namespace Sylius\MolliePlugin\Logger;

final class LoggerLevel
{
    public const NOTICE = 1;

    public const ERROR = 2;

    public const LOG_DISABLED = 0;

    public const LOG_ERRORS = 1;

    public const LOG_EVERYTHING = 2;
}
