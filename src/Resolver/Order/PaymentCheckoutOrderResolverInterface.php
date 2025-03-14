<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\Order;

use Sylius\MolliePlugin\Entity\OrderInterface;

interface PaymentCheckoutOrderResolverInterface
{
    public function resolve(): OrderInterface;
}
