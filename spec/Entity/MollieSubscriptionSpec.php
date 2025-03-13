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

namespace spec\SyliusMolliePlugin\Entity;

use SyliusMolliePlugin\Entity\MollieSubscription;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Entity\OrderInterface as MollieOrderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

class MollieSubscriptionSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieSubscription::class);
    }

    function it_implements_subscription_interface(): void
    {
        $this->shouldImplement(MollieSubscriptionInterface::class);
    }

    function it_has_null_id_by_default(): void
    {
        $this->getId()->shouldReturn(null);
    }

    function it_gets_order(OrderInterface $order): void
    {
        $this->addOrder($order);
        $this->getOrders()->shouldBeLike(new ArrayCollection([
            $order->getWrappedObject()
        ]));
    }

    function it_gets_state(): void
    {
        $this->setState('active');
        $this->getState()->shouldReturn('active');
    }

    function it_gets_payment_state(): void
    {
        $this->setPaymentState('active');
        $this->getPaymentState()->shouldReturn('active');
    }

    function it_gets_last_order(
        OrderInterface $order1,
        OrderInterface $order2
    ): void {
        $this->addOrder($order1);
        $this->addOrder($order2);

        $this->getLastOrder()->shouldReturn($order2);
        $this->getLastOrder()->shouldHaveType(OrderInterface::class);
    }

    function it_has_zero_recent_failed_payments_count_by_default(): void
    {
        $this->getRecentFailedPaymentsCount()->shouldReturn(0);
    }

    function it_increment_failed_payment_counter(): void
    {
        $this->getRecentFailedPaymentsCount()->shouldReturn(0);
        $this->incrementFailedPaymentCounter();
        $this->getRecentFailedPaymentsCount()->shouldReturn(1);
    }

    function it_reset_failed_payment_count(): void
    {
        $this->getRecentFailedPaymentsCount()->shouldReturn(0);
        $this->incrementFailedPaymentCounter();
        $this->getRecentFailedPaymentsCount()->shouldReturn(1);
        $this->resetFailedPaymentCount();
        $this->getRecentFailedPaymentsCount()->shouldReturn(0);
    }

    function it_gets_processing_state(): void
    {
        $this->setProcessingState('closed');
        $this->getProcessingState()->shouldReturn('closed');
    }

    function it_gets_order_item(OrderItemInterface $orderItem): void
    {
        $this->setOrderItem($orderItem);
        $this->getOrderItem()->shouldReturn($orderItem);
    }

    function it_gets_first_order(
        OrderItemInterface $orderItem,
        MollieOrderInterface $order
    ): void {
        $this->setOrderItem($orderItem);
        $orderItem->getOrder()->willReturn($order);

        $this->getFirstOrder()->shouldReturn($order);
    }

    function it_gets_customer(CustomerInterface $customer): void
    {
        $this->setCustomer($customer);
        $this->getCustomer()->shouldReturn($customer);
    }

    function it_gets_started_at(\DateTime $time): void
    {
        $time->setDate(2099,12,12);
        $this->setStartedAt($time);
        $this->getStartedAt()->shouldReturn($time);
    }

}
