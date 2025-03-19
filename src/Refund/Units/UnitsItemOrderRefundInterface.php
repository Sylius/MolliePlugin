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

namespace Sylius\MolliePlugin\Refund\Units;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MolliePlugin\DTO\PartialRefundItems;

interface UnitsItemOrderRefundInterface
{
    public function refund(OrderInterface $order, PartialRefundItems $partialRefundItems): array;

    public function getActualRefundedQuantity(OrderInterface $order, int $itemId): int;
}
