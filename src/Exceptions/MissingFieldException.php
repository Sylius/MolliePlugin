<?php


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
