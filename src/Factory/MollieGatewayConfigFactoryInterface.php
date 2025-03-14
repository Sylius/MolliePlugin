<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Payments\Methods\MethodInterface;

interface MollieGatewayConfigFactoryInterface extends FactoryInterface
{
    public function create(
        MethodInterface $method,
        GatewayConfigInterface $gateway,
        int $key
    ): MollieGatewayConfigInterface;
}
