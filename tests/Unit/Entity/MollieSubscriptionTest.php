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

namespace Tests\Sylius\MolliePlugin\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\MolliePlugin\Entity\MollieSubscription;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

final class MollieSubscriptionTest extends TestCase
{
    private MollieSubscriptionInterface $mollieSubscription;

    protected function setUp(): void
    {
        $this->mollieSubscription = new MollieSubscription();
    }

    public function testImplementsSubscriptionInterface(): void
    {
        $this->assertInstanceOf(MollieSubscriptionInterface::class, $this->mollieSubscription);
    }

    public function testHasNullIdByDefault(): void
    {
        $this->assertNull($this->mollieSubscription->getId());
    }

    public function testGetsOrder(): void
    {
        $orderMock = $this->createMock(OrderInterface::class);
        $this->mollieSubscription->addOrder($orderMock);
        $this->assertEquals(new ArrayCollection([$orderMock]), $this->mollieSubscription->getOrders());
    }

    public function testGetsState(): void
    {
        $this->mollieSubscription->setState('active');
        $this->assertSame('active', $this->mollieSubscription->getState());
    }

    public function testGetsPaymentState(): void
    {
        $this->mollieSubscription->setPaymentState('active');
        $this->assertSame('active', $this->mollieSubscription->getPaymentState());
    }

    public function testGetsLastOrder(): void
    {
        $order1Mock = $this->createMock(OrderInterface::class);
        $order2Mock = $this->createMock(OrderInterface::class);
        $this->mollieSubscription->addOrder($order1Mock);
        $this->mollieSubscription->addOrder($order2Mock);
        $this->assertSame($order2Mock, $this->mollieSubscription->getLastOrder());
        $this->assertInstanceOf(OrderInterface::class, $this->mollieSubscription->getLastOrder());
    }

    public function testHasZeroRecentFailedPaymentsCountByDefault(): void
    {
        $this->assertSame(0, $this->mollieSubscription->getRecentFailedPaymentsCount());
    }

    public function testIncrementFailedPaymentCounter(): void
    {
        $this->assertSame(0, $this->mollieSubscription->getRecentFailedPaymentsCount());
        $this->mollieSubscription->incrementFailedPaymentCounter();
        $this->assertSame(1, $this->mollieSubscription->getRecentFailedPaymentsCount());
    }

    public function testResetFailedPaymentCount(): void
    {
        $this->assertSame(0, $this->mollieSubscription->getRecentFailedPaymentsCount());
        $this->mollieSubscription->incrementFailedPaymentCounter();
        $this->assertSame(1, $this->mollieSubscription->getRecentFailedPaymentsCount());
        $this->mollieSubscription->resetFailedPaymentCount();
        $this->assertSame(0, $this->mollieSubscription->getRecentFailedPaymentsCount());
    }

    public function testGetsProcessingState(): void
    {
        $this->mollieSubscription->setProcessingState('closed');
        $this->assertSame('closed', $this->mollieSubscription->getProcessingState());
    }

    public function testGetsOrderItem(): void
    {
        $orderItemMock = $this->createMock(OrderItemInterface::class);
        $this->mollieSubscription->setOrderItem($orderItemMock);
        $this->assertSame($orderItemMock, $this->mollieSubscription->getOrderItem());
    }

    public function testGetsFirstOrder(): void
    {
        $orderItemMock = $this->createMock(OrderItemInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $this->mollieSubscription->setOrderItem($orderItemMock);
        $orderItemMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $this->assertSame($orderMock, $this->mollieSubscription->getFirstOrder());
    }

    public function testGetsCustomer(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);
        $this->mollieSubscription->setCustomer($customerMock);
        $this->assertSame($customerMock, $this->mollieSubscription->getCustomer());
    }

    public function testGetsStartedAt(): void
    {
        $timeMock = new \DateTime('2099-12-12');
        $this->mollieSubscription->setStartedAt($timeMock);
        $this->assertSame($timeMock, $this->mollieSubscription->getStartedAt());
    }
}
