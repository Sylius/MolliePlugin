<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Validator\Constraints;

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
