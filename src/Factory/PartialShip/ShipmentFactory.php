<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory\PartialShip;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ShipmentFactory implements ShipmentFactoryInterface
{
    /** @var FactoryInterface */
    private $baseFactory;

    public function __construct(FactoryInterface $baseFactory)
    {
        $this->baseFactory = $baseFactory;
    }

    public function createWithOrderInventorySourceAndMethodFromShipment(ShipmentInterface $shipment): ShipmentInterface
    {
        /** @var ShipmentInterface $newShipment */
        $newShipment = $this->baseFactory->createNew();

        $newShipment->setMethod($shipment->getMethod());

        /** @var OrderInterface $order */
        $order = $shipment->getOrder();
        $order->addShipment($newShipment);

        return $newShipment;
    }
}
