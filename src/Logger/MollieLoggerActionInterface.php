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

use Symfony\Component\HttpFoundation\Response;

interface MollieLoggerActionInterface
{
    public function addLog(
        string $message,
        int $logLevel = LoggerLevel::NOTICE,
        int $errorCode = Response::HTTP_OK,
    ): void;

    public function addNegativeLog(
        string $message,
        int $logLevel = LoggerLevel::ERROR,
        int $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR,
    ): void;
}
