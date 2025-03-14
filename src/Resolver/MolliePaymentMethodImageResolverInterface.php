<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;

interface MolliePaymentMethodImageResolverInterface
{
    public function resolve(MollieGatewayConfigInterface $paymentMethod): string;
}
