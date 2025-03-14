<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Order;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

interface SubscriptionOrderClonerInterface
{
    public function clone(
        MollieSubscriptionInterface $subscription,
        OrderInterface $order,
        OrderItemInterface $orderItem
    ): OrderInterface;
}
