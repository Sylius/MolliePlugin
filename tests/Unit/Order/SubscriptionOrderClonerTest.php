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

namespace Tests\Sylius\MolliePlugin\Unit\Order;

use Composer\InstalledVersions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Order\Model\OrderInterface as SyliusOrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface as LegacyFactoryInterface;
use Sylius\Component\Resource\Generator\RandomnessGeneratorInterface;
use Sylius\MolliePlugin\Cloner\AdjustmentClonerInterface;
use Sylius\MolliePlugin\Cloner\OrderItemClonerInterface;
use Sylius\MolliePlugin\Cloner\ShipmentClonerInterface;
use Sylius\MolliePlugin\Cloner\SubscriptionOrderCloner;
use Sylius\MolliePlugin\Cloner\SubscriptionOrderClonerInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class SubscriptionOrderClonerTest extends TestCase
{
    private OrderItemClonerInterface $orderItemClonerMock;

    private FactoryInterface|LegacyFactoryInterface $orderFactoryMock;

    private RandomnessGeneratorInterface $generatorMock;

    private AdjustmentClonerInterface $adjustmentClonerMock;

    private ShipmentClonerInterface $shipmentClonerMock;

    private SubscriptionOrderCloner $subscriptionOrderCloner;

    protected function setUp(): void
    {
        $version = InstalledVersions::getVersion('sylius/resource-bundle');
        if ($version < '1.11.0') {
            $this->orderFactoryMock = $this->createMock(LegacyFactoryInterface::class);
        } else {
            $this->orderFactoryMock = $this->createMock(FactoryInterface::class);
        }
        $this->orderItemClonerMock = $this->createMock(OrderItemClonerInterface::class);
        $this->generatorMock = $this->createMock(RandomnessGeneratorInterface::class);
        $this->adjustmentClonerMock = $this->createMock(AdjustmentClonerInterface::class);
        $this->shipmentClonerMock = $this->createMock(ShipmentClonerInterface::class);
        $this->subscriptionOrderCloner = new SubscriptionOrderCloner($this->orderItemClonerMock, $this->orderFactoryMock, $this->generatorMock, $this->adjustmentClonerMock, $this->shipmentClonerMock);
    }

    public function testImplementsSubscriptionOrderClonerInterface(): void
    {
        $this->assertInstanceOf(SubscriptionOrderClonerInterface::class, $this->subscriptionOrderCloner);
    }

    public function testClonesSubscriptionOrder(): void
    {
        $orderMock = $this->createMock(\Sylius\MolliePlugin\Entity\OrderInterface::class);
        $clonedOrderMock = $this->createMock(\Sylius\MolliePlugin\Entity\OrderInterface::class);
        $rootOrderMock = $this->createMock(\Sylius\MolliePlugin\Entity\OrderInterface::class);
        $subscriptionMock = $this->createMock(\Sylius\MolliePlugin\Entity\MollieSubscriptionInterface::class);
        $orderItemMock = $this->createMock(\Sylius\Component\Core\Model\OrderItemInterface::class);
        $clonedItemMock = $this->createMock(\Sylius\Component\Core\Model\OrderItemInterface::class);
        $channelMock = $this->createMock(\Sylius\Component\Channel\Model\ChannelInterface::class);
        $customerMock = $this->createMock(\Sylius\Component\Customer\Model\CustomerInterface::class);
        $couponMock = $this->createMock(\Sylius\Component\Promotion\Model\PromotionCouponInterface::class);
        $addressMock = $this->createMock(\Sylius\Component\Core\Model\AddressInterface::class);
        $billingAddressMock = $this->createMock(\Sylius\Component\Core\Model\AddressInterface::class);
        $adjustmentMock = $this->createMock(\Sylius\Component\Core\Model\AdjustmentInterface::class);
        $clonedOrderAdjustmentMock = $this->createMock(\Sylius\Component\Core\Model\AdjustmentInterface::class);

        $subscriptionMock->expects($this->once())
            ->method('getFirstOrder')
            ->willReturn($rootOrderMock)
        ;

        $rootOrderMock->expects($this->once())
            ->method('getNumber')
            ->willReturn('#001')
        ;

        $this->orderFactoryMock->expects($this->once())
            ->method('createNew')
            ->willReturn($clonedOrderMock)
        ;

        $subscriptionMock->expects($this->once())
            ->method('getOrders')
            ->willReturn(new ArrayCollection([$orderMock]))
        ;

        $subscriptionMock->expects($this->once())
            ->method('getId')
            ->willReturn(3)
        ;

        $orderMock->expects($this->once())
            ->method('getNotes')
            ->willReturn('test_notes')
        ;
        $orderMock->expects($this->once())
            ->method('isAbandonedEmail')
            ->willReturn(false)
        ;
        $orderMock->expects($this->once())
            ->method('getChannel')
            ->willReturn($channelMock)
        ;
        $orderMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock)
        ;
        $orderMock->expects($this->once())
            ->method('getCurrencyCode')
            ->willReturn('EUR')
        ;
        $orderMock->expects($this->once())
            ->method('getCustomerIp')
            ->willReturn('127.0.0.0')
        ;
        $orderMock->expects($this->once())
            ->method('getLocaleCode')
            ->willReturn('en_US')
        ;
        $orderMock->expects($this->once())
            ->method('getPromotionCoupon')
            ->willReturn($couponMock)
        ;
        $orderMock->expects($this->exactly(2))
        ->method('getShippingAddress')
            ->willReturn($addressMock)
        ;
        $orderMock->expects($this->exactly(2))
        ->method('getBillingAddress')
            ->willReturn($billingAddressMock)
        ;
        $orderMock->expects($this->once())
            ->method('getAdjustments')
            ->willReturn(new ArrayCollection([$adjustmentMock]))
        ;
        $orderMock->expects($this->never())
        ->method('getShipments')
        ;

        $clonedOrderMock->expects($this->once())
            ->method('setRecurringSequenceIndex')
            ->with(1)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setState')
            ->with(SyliusOrderInterface::STATE_NEW)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setNotes')
            ->with('test_notes')
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setAbandonedEmail')
            ->with(false)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setChannel')
            ->with($channelMock)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setCheckoutState')
            ->with(OrderCheckoutStates::STATE_COMPLETED)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setCheckoutCompletedAt')
            ->with($this->isInstanceOf(\DateTime::class))
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setCreatedAt')
            ->with($this->isInstanceOf(\DateTime::class))
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setCustomer')
            ->with($customerMock)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setCurrencyCode')
            ->with('EUR')
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setCustomerIp')
            ->with('127.0.0.0')
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setLocaleCode')
            ->with('en_US')
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setPaymentState')
            ->with(OrderPaymentStates::STATE_AWAITING_PAYMENT)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setPromotionCoupon')
            ->with($couponMock)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($addressMock)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddressMock)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setShippingState')
            ->with(OrderShippingStates::STATE_READY)
        ;
        $clonedOrderMock->expects($this->once())
            ->method('setTokenValue')
            ->with($this->isType('string'))
        ;
        $clonedOrderMock->expects($this->once())
            ->method('recalculateAdjustmentsTotal')
        ;
        $clonedOrderMock->expects($this->once())
            ->method('recalculateItemsTotal')
        ;

        $this->orderItemClonerMock->expects($this->once())
            ->method('clone')
            ->with($orderItemMock, $clonedOrderMock)
            ->willReturn($clonedItemMock)
        ;

        $clonedOrderMock->expects($this->once())
            ->method('addItem')
            ->with($clonedItemMock)
        ;

        $this->adjustmentClonerMock->expects($this->once())
            ->method('clone')
            ->with($adjustmentMock)
            ->willReturn($clonedOrderAdjustmentMock)
        ;

        $clonedOrderMock->expects($this->once())
            ->method('addAdjustment')
            ->with($clonedOrderAdjustmentMock)
        ;

        $clonedOrderMock->expects($this->once())
            ->method('isShippingRequired')
            ->willReturn(false)
        ;

        $result = $this->subscriptionOrderCloner->clone($subscriptionMock, $orderMock, $orderItemMock);
        $this->assertSame($clonedOrderMock, $result);
    }
}
