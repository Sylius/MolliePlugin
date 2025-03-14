<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Remover\PartialShip;

use Sylius\MolliePlugin\DTO\PartialShipItems;
use Sylius\Component\Core\Model\OrderInterface;

interface OldShipmentItemsRemoverInterface
{
    public function remove(OrderInterface $order, PartialShipItems $shipItems): OrderInterface;
}
