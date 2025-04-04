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

namespace Sylius\MolliePlugin\Cloner;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Order\Model\OrderInterface as SyliusOrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Generator\RandomnessGeneratorInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Model\AdjustmentInterface;
use Webmozart\Assert\Assert;

final class SubscriptionOrderCloner implements SubscriptionOrderClonerInterface
{
    public function __construct(
        private readonly OrderItemClonerInterface $orderItemCloner,
        private readonly FactoryInterface $orderFactory,
        private readonly RandomnessGeneratorInterface $randomnessGenerator,
        private readonly AdjustmentClonerInterface $adjustmentCloner,
        private readonly ShipmentClonerInterface $shipmentCloner,
    ) {
    }

    public function clone(
        MollieSubscriptionInterface $subscription,
        OrderInterface $order,
        OrderItemInterface $orderItem,
    ): OrderInterface {
        $rootOrder = $subscription->getFirstOrder();

        /** @var OrderInterface $clonedOrder */
        $clonedOrder = $this->orderFactory->createNew();
        $ordersCount = $subscription->getOrders()->count();
        $orderNumberSequence = $ordersCount + 1;

        Assert::notNull($rootOrder);
        $clonedOrder->setNumber(
            sprintf('%s-%d-%d', $rootOrder->getNumber(), $subscription->getId(), $orderNumberSequence),
        );
        $clonedOrder->setRecurringSequenceIndex($ordersCount);
        $clonedOrder->setState(SyliusOrderInterface::STATE_NEW);
        $clonedOrder->setNotes($order->getNotes());
        $clonedOrder->setAbandonedEmail($order->isAbandonedEmail());
        $clonedOrder->setChannel($order->getChannel());
        $clonedOrder->setCheckoutState(OrderCheckoutStates::STATE_COMPLETED);
        $clonedOrder->setCheckoutCompletedAt(new \DateTime());
        $clonedOrder->setCustomer($order->getCustomer());
        $clonedOrder->setCreatedAt(new \DateTime());
        $clonedOrder->setCurrencyCode($order->getCurrencyCode());
        $clonedOrder->setCustomerIp($order->getCustomerIp());
        $clonedOrder->setLocaleCode($order->getLocaleCode());
        $clonedOrder->setPaymentState(OrderPaymentStates::STATE_AWAITING_PAYMENT);
        $clonedOrder->setPromotionCoupon($order->getPromotionCoupon());

        Assert::notNull($order->getShippingAddress());
        Assert::notNull($order->getBillingAddress());
        $clonedOrder->setShippingAddress(clone $order->getShippingAddress());
        $clonedOrder->setBillingAddress(clone $order->getBillingAddress());
        $clonedOrder->setShippingState(OrderShippingStates::STATE_READY);
        $clonedOrder->setTokenValue($this->randomnessGenerator->generateUriSafeString(10));

        $clonedItem = $this->orderItemCloner->clone($orderItem, $clonedOrder);
        $clonedOrder->addItem($clonedItem);

        /** @var AdjustmentInterface $adjustment */
        foreach ($order->getAdjustments() as $adjustment) {
            if (\Sylius\Component\Core\Model\AdjustmentInterface::SHIPPING_ADJUSTMENT === $adjustment->getType()) {
                continue;
            }
            $clonedOrderAdjustment = $this->adjustmentCloner->clone($adjustment);

            $clonedOrder->addAdjustment($clonedOrderAdjustment);
        }

        if ($clonedOrder->isShippingRequired()) {
            foreach ($order->getShipments() as $shipment) {
                $clonedShipment = $this->shipmentCloner->clone($shipment);
                $clonedOrder->addShipment($clonedShipment);

                foreach ($clonedOrder->getItemUnits() as $unit) {
                    $clonedShipment->addUnit($unit);
                }

                /** @var AdjustmentInterface $adjustment */
                foreach ($shipment->getAdjustments() as $adjustment) {
                    /** @var AdjustmentInterface $clonedAdjustment */
                    $clonedAdjustment = $this->adjustmentCloner->clone($adjustment);

                    $clonedShipment->addAdjustment($clonedAdjustment);
                    $clonedAdjustment->setShipment($clonedShipment);
                    $clonedAdjustment->setAdjustable($clonedOrder);
                }
            }
        }

        $clonedOrder->recalculateAdjustmentsTotal();
        $clonedOrder->recalculateItemsTotal();

        return $clonedOrder;
    }
}
