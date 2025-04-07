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

namespace Sylius\MolliePlugin\Refund\Generator;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MolliePlugin\Model\DTO\PartialRefundItem;
use Sylius\MolliePlugin\Model\DTO\PartialRefundItems;

final class PaymentNewUnitRefundGenerator implements PaymentNewUnitRefundGeneratorInterface
{
    public function generate(OrderInterface $order, PartialRefundItems $partialRefundItems): PartialRefundItems
    {
        $units = $order->getItemUnits();

        foreach ($units as $unit) {
            $partialRefundItem = $partialRefundItems->findById($unit->getId());

            if (null !== $partialRefundItem) {
                $partialRefundItem->setAmountTotal($unit->getTotal());

                continue;
            }

            $partialRefundItem = new PartialRefundItem();

            $partialRefundItem->setId($unit->getId());
            $partialRefundItem->setAmountRefunded(0);
            $partialRefundItem->setAmountTotal($unit->getTotal());

            $partialRefundItems->setPartialRefundItems($partialRefundItem);
        }

        return $partialRefundItems;
    }
}
