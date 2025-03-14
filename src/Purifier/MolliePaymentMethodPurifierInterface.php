<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Purifier;

interface MolliePaymentMethodPurifierInterface
{
    public function removeMethod(string $methodId): void;

    public function removeAllNoLongerSupportedMethods(): void;
}
