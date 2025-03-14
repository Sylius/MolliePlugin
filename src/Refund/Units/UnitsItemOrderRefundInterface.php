<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Refund\Units;

use Sylius\MolliePlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;

interface UnitsItemOrderRefundInterface
{
    public function refund(OrderInterface $order, PartialRefundItems $partialRefundItems): array;

    public function getActualRefundedQuantity(OrderInterface $order, int $itemId): int;
}
