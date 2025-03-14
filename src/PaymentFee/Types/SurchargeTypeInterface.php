<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\PaymentFee\Types;

use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\Component\Order\Model\OrderInterface;

interface SurchargeTypeInterface
{
    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): OrderInterface;

    public function canCalculate(string $type): bool;
}
