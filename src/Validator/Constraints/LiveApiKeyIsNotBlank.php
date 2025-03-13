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

namespace SyliusMolliePlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class LiveApiKeyIsNotBlank extends Constraint
{
    /** @var string */
    public $message = 'sylius_mollie_plugin.api_key.not_blank';

    /** @var string */
    public $field;

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return parent::validatedBy();
    }
}
