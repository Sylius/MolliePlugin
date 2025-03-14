<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\ApplePayDirect;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

interface ApplePayDirectApiOrderPaymentResolverInterface
{
    public function resolve(
        OrderInterface $order,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        array $details
    ): void;
}
