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

namespace Sylius\MolliePlugin\Exceptions;

final class MollieMethodsException extends \RuntimeException
{
    public const RETRIEVAL_CODE = 0;

    public const UPDATE_CODE = 1;

    public static function retrieval(?\Throwable $previous = null): self
    {
        return new self(
            'An error occurred while trying to retrieve Mollie payment methods. Please check your Mollie API key and network connection.',
            self::RETRIEVAL_CODE,
            $previous,
        );
    }

    public static function update(?\Throwable $throwable = null): self
    {
        return new self(
            'An error occurred while trying to update Mollie payment methods. Please check your Mollie API key and network connection.',
            self::UPDATE_CODE,
            $throwable,
        );
    }
}
