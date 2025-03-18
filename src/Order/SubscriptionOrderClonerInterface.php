<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\MolliePlugin\Order;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

interface SubscriptionOrderClonerInterface
{
    public function clone(
        MollieSubscriptionInterface $subscription,
        OrderInterface $order,
        OrderItemInterface $orderItem,
    ): OrderInterface;
}
