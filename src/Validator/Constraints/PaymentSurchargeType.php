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

final class PaymentSurchargeType extends Constraint
{
    /** @var string */
    public $message = 'sylius_mollie_plugin.form.error.payment_surcharge_not_empty';

    public function validatedBy(): string
    {
        return PaymentSurchargeTypeValidator::class;
    }

    /** @return string[] */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
