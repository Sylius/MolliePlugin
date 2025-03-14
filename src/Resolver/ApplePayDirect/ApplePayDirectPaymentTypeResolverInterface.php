<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\ApplePayDirect;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface ApplePayDirectPaymentTypeResolverInterface
{
    public function resolve(
        MollieGatewayConfigInterface $mollieGatewayConfig,
        PaymentInterface $payment,
        array $applePayDirectToken
    ): void;
}
