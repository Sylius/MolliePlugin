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

class MollieGatewayConfigValidatorType extends Constraint
{
    public string $minGreaterThanMaxMessage = 'sylius_mollie.form.error.min_greater_than_max';

    public string $minLessThanMollieMinMessage = 'sylius_mollie.form.error.min_less_than_mollie_min';

    public string $maxGreaterThanMollieMaxMessage = 'sylius_mollie.form.error.max_greater_than_mollie_max';

    public function validatedBy(): string
    {
        return MollieGatewayConfigValidator::class;
    }

    /** @return string[] */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
