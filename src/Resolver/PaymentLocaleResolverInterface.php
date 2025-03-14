<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\Component\Core\Model\OrderInterface;

interface PaymentLocaleResolverInterface
{
    public function resolveFromOrder(OrderInterface $order): ?string;
}
