<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\ApplePayDirect;

use Sylius\MolliePlugin\Entity\OrderInterface;

interface ApplePayDirectApiPaymentResolverInterface
{
    public function resolve(OrderInterface $order, array $details): void;
}
