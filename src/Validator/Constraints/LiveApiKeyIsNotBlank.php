<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Validator\Constraints;

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
