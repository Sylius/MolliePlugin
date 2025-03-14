<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Order;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

interface OrderItemClonerInterface
{
    public function clone(OrderItemInterface $orderItem, OrderInterface $order): OrderItemInterface;
}
