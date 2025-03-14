<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Checker\ApplePay;

interface ApplePayEnabledCheckerInterface
{
    public function isEnabled(): bool;
}
