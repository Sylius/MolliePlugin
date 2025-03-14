<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;

interface PaymentMethodConfigResolverInterface
{
    public function getConfigFromMethodId(string $methodId): MollieGatewayConfigInterface;
}
