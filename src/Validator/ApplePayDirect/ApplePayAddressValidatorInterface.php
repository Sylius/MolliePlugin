<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Validator\ApplePayDirect;

interface ApplePayAddressValidatorInterface
{
    public function validate(array $data): void;
}
