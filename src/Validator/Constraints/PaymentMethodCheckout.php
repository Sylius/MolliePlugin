<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class PaymentMethodCheckout extends Constraint
{
    /** @var string */
    public $message = 'sylius_mollie_plugin.empty_payment_method_checkout';

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
