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

namespace Sylius\MolliePlugin\StateMachine\Applicator;

use Mollie\Api\Resources\Order;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Shipping\ShipmentTransitions;

final class MollieOrderStatesApplicator implements MollieOrderStatesApplicatorInterface
{
    public function __construct(
        private readonly StateMachineInterface $stateMachine,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function execute(Order $order): void
    {
        if (null === $order->orderNumber) {
            return;
        }

        /** @var OrderInterface|null $orderSylius */
        $orderSylius = $this->orderRepository->findOneBy(['number' => $order->orderNumber]);
        if (null === $orderSylius) {
            return;
        }

        /** @var ShipmentInterface|false $lastShipment */
        $lastShipment = $orderSylius->getShipments()->last();
        if (false === $lastShipment) {
            return;
        }

        if ($order->isCompleted()) {
            $this->applyOrderTransition($orderSylius, OrderTransitions::TRANSITION_FULFILL);
            $this->applyShipmentTransition($lastShipment, ShipmentTransitions::TRANSITION_SHIP);
        }

        if ($order->isCanceled() || $order->isExpired()) {
            $this->applyOrderTransition($orderSylius, OrderTransitions::TRANSITION_CANCEL);
        }

        if ($order->isShipping()) {
            $this->applyShipmentTransition($lastShipment, ShipmentTransitions::TRANSITION_SHIP);
        }
    }

    private function applyOrderTransition(OrderInterface $orderSylius, string $transition): void
    {
        if (!$this->stateMachine->can($orderSylius, OrderTransitions::GRAPH, $transition)) {
            return;
        }

        $this->stateMachine->apply($orderSylius, OrderTransitions::GRAPH, $transition);
    }

    private function applyShipmentTransition(ShipmentInterface $shipment, string $transition): void
    {
        if (!$this->stateMachine->can($shipment, ShipmentTransitions::GRAPH, $transition)) {
            return;
        }

        $this->stateMachine->apply($shipment, ShipmentTransitions::GRAPH, $transition);
    }
}
