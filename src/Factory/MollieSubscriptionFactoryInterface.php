<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface MollieSubscriptionFactoryInterface extends FactoryInterface
{
    public function createFromFirstOrder(OrderInterface $order): MollieSubscriptionInterface;

    public function createFromFirstOrderWithOrderItemAndPaymentConfiguration(
        OrderInterface $order,
        OrderItemInterface $orderItem,
        array $paymentConfiguration = [],
        string $mandateId = null
    ): MollieSubscriptionInterface;
}
