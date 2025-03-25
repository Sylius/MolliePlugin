<?php

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
