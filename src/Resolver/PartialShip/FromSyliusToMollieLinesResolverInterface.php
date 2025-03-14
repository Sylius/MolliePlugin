<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\PartialShip;

use Sylius\MolliePlugin\DTO\PartialShipItems;
use Doctrine\Common\Collections\Collection;
use Mollie\Api\Resources\Order;

interface FromSyliusToMollieLinesResolverInterface
{
    public function resolve(Collection $units, Order $mollieOrder): PartialShipItems;
}
