<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Helper;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentDescriptionInterface
{
    public const PAYPAL_DESCRIPTION = 'Order';

    public function getPaymentDescription(
        PaymentInterface $payment,
        MollieGatewayConfigInterface $methodConfig,
        OrderInterface $order
    ): string;
}
