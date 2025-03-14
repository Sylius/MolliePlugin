<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider\Apple;

use Sylius\MolliePlugin\Entity\OrderInterface;

interface ApplePayDirectPaymentProviderInterface
{
    public function provideApplePayPayment(OrderInterface $order, array $applePayPaymentToken): void;
}
