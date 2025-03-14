<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Purifier\PartialShip;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderShipmentPurifierInterface
{
    public function purify(OrderInterface $order): void;
}
