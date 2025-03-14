<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Entity\OrderInterface;

interface MollieFactoryNameResolverInterface
{
    public function resolve(OrderInterface $order = null): string;
}
