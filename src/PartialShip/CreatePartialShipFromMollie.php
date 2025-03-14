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

namespace Sylius\MolliePlugin\PartialShip;

use Sylius\MolliePlugin\Factory\PartialShip\ShipmentFactoryInterface;
use Sylius\MolliePlugin\Resolver\PartialShip\FromMollieToSyliusResolverInterface;
use Doctrine\Common\Collections\Collection;
use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CreatePartialShipFromMollie implements CreatePartialShipFromMollieInterface
{
    /** @var ShipmentFactoryInterface */
    private $shipmentFactory;

    /** @var RepositoryInterface */
    private $orderRepository;

    /** @var FromMollieToSyliusResolverInterface */
    private $fromMollieToSyliusResolver;

    public function __construct(
        ShipmentFactoryInterface $shipmentFactory,
        RepositoryInterface $orderRepository,
        FromMollieToSyliusResolverInterface $fromMollieToSyliusResolver
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->orderRepository = $orderRepository;
        $this->fromMollieToSyliusResolver = $fromMollieToSyliusResolver;
    }

    public function create(OrderInterface $order, Order $mollieOrder): OrderInterface
    {
        $newOrder = $this->fromMollieToSyliusResolver->resolve($order, $mollieOrder);

        /** @var ShipmentInterface $shipment */
        $shipment = $newOrder->getShipments()->first();
        $this->shipmentFactory->createWithOrderInventorySourceAndMethodFromShipment($shipment);

        /** @var Collection $shipments */
        $shipments = $order->getShipments();
        $shipmentsToRemove = $shipments->filter(static function (ShipmentInterface $shipment): bool {
            return ShipmentInterface::STATE_READY === $shipment->getState() && $shipment->getUnits()->isEmpty();
        });

        foreach ($shipmentsToRemove as $shipmentToRemove) {
            $order->removeShipment($shipmentToRemove);
        }

        $this->orderRepository->add($order);

        return $order;
    }
}
