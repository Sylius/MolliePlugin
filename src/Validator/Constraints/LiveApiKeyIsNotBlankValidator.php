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

namespace Sylius\MolliePlugin\Validator\Constraints;

use Sylius\MolliePlugin\Exceptions\MissingFieldException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class LiveApiKeyIsNotBlankValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof LiveApiKeyIsNotBlank) {
            throw new UnexpectedTypeException($constraint, LiveApiKeyIsNotBlank::class);
        }

        if (!array_key_exists('environment', $value)) {
            throw new MissingFieldException('environment');
        }

        if (!array_key_exists('api_key_live', $value)) {
            throw new MissingFieldException('api_key_live');
        }

        $isEmpty = null === $value['api_key_live'] || '' === $value['api_key_live'];
        if (true === $value['environment'] && $isEmpty) {
            $this->context->buildViolation($constraint->message)
                ->atPath(sprintf('[%s]', $constraint->field))
                ->addViolation()
            ;
        }
    }
}
