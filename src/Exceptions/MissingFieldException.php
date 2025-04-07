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

use Symfony\Component\Validator\Exception\ValidatorException;

final class MissingFieldException extends ValidatorException
{
    public function __construct(string $field)
    {
        parent::__construct(sprintf("Expected field %s, but it's missing.", $field));
    }
}
