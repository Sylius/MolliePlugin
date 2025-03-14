<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Refund\Units;

use Sylius\Component\Core\Model\OrderInterface;

interface ShipmentUnitRefundInterface
{
    public function refund(
        OrderInterface $order,
        array $orderItemUnitRefund,
        int $totalToRefund
    ): array;
}
