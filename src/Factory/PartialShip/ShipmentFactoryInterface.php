<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory\PartialShip;

use Sylius\Component\Core\Model\ShipmentInterface;

interface ShipmentFactoryInterface
{
    public function createWithOrderInventorySourceAndMethodFromShipment(ShipmentInterface $shipment): ShipmentInterface;
}
