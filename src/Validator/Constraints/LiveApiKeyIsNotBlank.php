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

use Symfony\Component\Validator\Constraint;

final class LiveApiKeyIsNotBlank extends Constraint
{
    public string $message = 'sylius_mollie.api_key.not_blank';

    public string $field;

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return parent::validatedBy();
    }
}
