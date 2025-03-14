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

namespace Sylius\MolliePlugin\Action\StateMachine;

use Sylius\MolliePlugin\PartialShip\CreatePartialShipFromMollieInterface;
use Sylius\MolliePlugin\Transitions\PartialShip\ShipmentTransitions as ShipmentTransitionsPartial;
use Mollie\Api\Resources\Order;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Shipping\ShipmentTransitions;

final class SetStatusOrderAction implements SetStatusOrderActionInterface
{
    public function __construct(private readonly FactoryInterface $factory, private readonly OrderRepositoryInterface $orderRepository, private readonly CreatePartialShipFromMollieInterface $createPartialShipFromMollie)
    {
    }

    public function execute(Order $order): void
    {
        if (null === $order->orderNumber) {
            return;
        }
        /** @var OrderInterface $orderSylius */
        $orderSylius = $this->orderRepository->findOneBy(['number' => $order->orderNumber]);

        /** @var ShipmentInterface $firstShipment */
        $firstShipment = $orderSylius->getShipments()->first();

        /** @var ShipmentInterface $lastShipment */
        $lastShipment = $orderSylius->getShipments()->last();

        if ($order->isCompleted()) {
            $this->applyStateMachineOrderTransition($orderSylius, OrderTransitions::TRANSITION_FULFILL);
            $this->applyStateMachineShipmentsTransition($firstShipment, ShipmentTransitions::TRANSITION_SHIP);
        }
        if ($order->isCanceled() || $order->isExpired()) {
            $this->applyStateMachineOrderTransition($orderSylius, OrderTransitions::TRANSITION_CANCEL);
        }

        if ($order->isShipping() && $this->isConfirmNotify($order, $firstShipment) && false === $this->isShippingAllItems($firstShipment)) {
            return;
        }
        if ($order->isShipping() && false === $this->isShippingAllItems($firstShipment)) {
            $this->createPartialShipFromMollie->create($orderSylius, $order);
            $this->applyStateMachineShipmentsTransition($lastShipment, ShipmentTransitionsPartial::TRANSITION_CREATE_AND_SHIP);
        }
        if ($order->isShipping() && true === $this->isShippingAllItems($firstShipment)) {
            $this->applyStateMachineShipmentsTransition($lastShipment, ShipmentTransitions::TRANSITION_SHIP);
        }
    }

    private function applyStateMachineOrderTransition(OrderInterface $orderSylius, string $transitions): void
    {
        $stateMachine = $this->factory->get($orderSylius, OrderTransitions::GRAPH);

        if (!$stateMachine->can($transitions)) {
            return;
        }

        $stateMachine->apply($transitions);
    }

    private function applyStateMachineShipmentsTransition(ShipmentInterface $orderSylius, string $transitions): void
    {
        $stateMachine = $this->factory->get($orderSylius, ShipmentTransitions::GRAPH);

        if (!$stateMachine->can($transitions)) {
            return;
        }

        $stateMachine->apply($transitions);
    }

    private function isShippingAllItems(ShipmentInterface $shipment): bool
    {
        return $shipment->getUnits()->isEmpty();
    }

    private function isConfirmNotify(Order $order, ShipmentInterface $shipment): bool
    {
        // check if in mollie and sylius is the same shipped items
        $shippableQuantity = 0;
        foreach ($order->lines as $line) {
            if (!property_exists($line, 'type')) {
                throw new \InvalidArgumentException();
            }
            if ('physical' === $line->type) {
                if (!property_exists($line, 'shippableQuantity')) {
                    throw new \InvalidArgumentException();
                }
                $shippableQuantity += $line->shippableQuantity;
            }
        }

        return $shippableQuantity === count($shipment->getUnits()->toArray());
    }
}
