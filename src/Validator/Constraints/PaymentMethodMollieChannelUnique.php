<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class PaymentMethodMollieChannelUnique extends Constraint
{
    /** @var string */
    public $message = 'sylius_mollie_plugin.form.error.channel_should_be_unique';

    public function validatedBy(): string
    {
        return PaymentMethodMollieChannelUniqueValidator::class;
    }

    /** @return string[] */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
