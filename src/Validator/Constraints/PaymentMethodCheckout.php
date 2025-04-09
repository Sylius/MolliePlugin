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

final class PaymentMethodCheckout extends Constraint
{
    public string $message = 'sylius_mollie.empty_payment_method_checkout';

    public function validatedBy(): string
    {
        return PaymentMethodCheckoutValidator::class;
    }

    /** @return string[] */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
