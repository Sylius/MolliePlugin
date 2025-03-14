<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\PartialShip;

use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;

interface FromMollieToSyliusResolverInterface
{
    public const SHIPPING_STATUS = 'shipping';

    public function resolve(OrderInterface $order, Order $mollieOrder): OrderInterface;
}
