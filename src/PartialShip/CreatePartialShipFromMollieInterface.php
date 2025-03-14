<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\PartialShip;

use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;

interface CreatePartialShipFromMollieInterface
{
    public function create(OrderInterface $order, Order $mollieOrder): OrderInterface;
}
