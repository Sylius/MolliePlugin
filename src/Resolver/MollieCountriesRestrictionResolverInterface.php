<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;

interface MollieCountriesRestrictionResolverInterface
{
    public function resolve(
        MollieGatewayConfigInterface $paymentMethod,
        array $methods,
        string $countryCode
    ): ?array;
}
