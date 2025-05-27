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

namespace Sylius\MolliePlugin\Menu;

use Sylius\Bundle\AdminBundle\Event\OrderShowMenuBuilderEvent;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Webmozart\Assert\Assert;

final class RemoveRefundsButtonOrderShowMenuListener
{
    public function removeRefundsButton(OrderShowMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null === $menu->getChild('refunds')) {
            return;
        }

        $order = $event->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        if (
            OrderPaymentStates::STATE_REFUNDED === $order->getPaymentState() &&
            PaymentInterface::STATE_REFUNDED === $order->getLastPayment()->getState()
        ) {
            $menu->removeChild('refunds');
        }
    }
}
