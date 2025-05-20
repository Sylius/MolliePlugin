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
use SM\Factory\FactoryInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Abstraction\StateMachine\WinzouStateMachineAdapter;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Shipping\ShipmentTransitions;
use Sylius\MolliePlugin\PartialShip\Converter\CreatePartialShipFromMollieInterface;
use Sylius\MolliePlugin\StateMachine\ShipmentTransitions as ShipmentTransitionsPartial;

final class MollieOrderStatesApplicator implements MollieOrderStatesApplicatorInterface
{
    public function __construct(
        private readonly FactoryInterface|StateMachineInterface $factory,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CreatePartialShipFromMollieInterface $createPartialShipFromMollie,
    ) {
        if ($this->factory instanceof FactoryInterface) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '2.2',
                sprintf(
                    'Passing an instance of "%s" as the first argument is deprecated. It will accept only instances of "%s" in MolliePlugin 3.0. The argument name will change from "stateMachineFactory" to "stateMachine".',
                    FactoryInterface::class,
                    StateMachineInterface::class,
                ),
            );
        }

        trigger_deprecation(
            'sylius/mollie-plugin',
            '2.2',
            sprintf(
                'The "%s" service will be removed in MolliePlugin 3.0 and should not be used anymore.',
                CreatePartialShipFromMollieInterface::class,
            ),
        );
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
            $this->applyOrderTransition($orderSylius, OrderTransitions::TRANSITION_FULFILL);
            $this->applyShipmentTransition($firstShipment, ShipmentTransitions::TRANSITION_SHIP);
        }

        if ($order->isCanceled() || $order->isExpired()) {
            $this->applyOrderTransition($orderSylius, OrderTransitions::TRANSITION_CANCEL);
        }

        if (
            $order->isShipping() &&
            $this->isConfirmNotify($order, $firstShipment) &&
            false === $this->isShippingAllItems($firstShipment)
        ) {
            return;
        }

        if ($order->isShipping() && false === $this->isShippingAllItems($firstShipment)) {
            $this->createPartialShipFromMollie->create($orderSylius, $order);
            $this->applyShipmentTransition(
                $lastShipment,
                ShipmentTransitionsPartial::TRANSITION_CREATE_AND_SHIP,
            );
        }

        if ($order->isShipping() && true === $this->isShippingAllItems($firstShipment)) {
            $this->applyShipmentTransition($lastShipment, ShipmentTransitions::TRANSITION_SHIP);
        }
    }

    private function applyOrderTransition(OrderInterface $orderSylius, string $transition): void
    {
        $stateMachine = $this->getStateMachine();
        if (!$stateMachine->can($orderSylius, OrderTransitions::GRAPH, $transition)) {
            return;
        }

        $stateMachine->apply($orderSylius, OrderTransitions::GRAPH, $transition);
    }

    private function applyShipmentTransition(ShipmentInterface $orderSylius, string $transition): void
    {
        $stateMachine = $this->getStateMachine();
        if (!$stateMachine->can($orderSylius, ShipmentTransitions::GRAPH, $transition)) {
            return;
        }

        $stateMachine->apply($orderSylius, ShipmentTransitions::GRAPH, $transition);
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

    private function getStateMachine(): StateMachineInterface
    {
        if ($this->factory instanceof FactoryInterface) {
            return new WinzouStateMachineAdapter($this->factory);
        }

        return $this->factory;
    }
}
