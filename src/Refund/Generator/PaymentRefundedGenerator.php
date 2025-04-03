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
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Model\DTO\PartialRefundItem;
use Sylius\MolliePlugin\Model\DTO\PartialRefundItems;
use Sylius\RefundPlugin\Entity\RefundInterface;
use Sylius\RefundPlugin\Model\RefundType;

final class PaymentRefundedGenerator implements PaymentRefundedGeneratorInterface
{
    public function __construct(private readonly RepositoryInterface $refundUnitsRepository)
    {
    }

    public function generate(OrderInterface $order): PartialRefundItems
    {
        $refundedUnits = $this->refundUnitsRepository->findBy([
            'order' => $order->getId(),
            'type' => RefundType::orderItemUnit(),
        ]);

        $partialRefundItems = new PartialRefundItems();

        /** @var RefundInterface $refundedUnit */
        foreach ($refundedUnits as $refundedUnit) {
            $partialRefundItem = new PartialRefundItem();

            $partialRefund = $partialRefundItems->findById($refundedUnit->getRefundedUnitId());

            if (null !== $partialRefund) {
                $partialRefund->setAmountRefunded($refundedUnit->getAmount());

                continue;
            }

            $partialRefundItem->setId($refundedUnit->getRefundedUnitId());
            $partialRefundItem->setAmountRefunded($refundedUnit->getAmount());

            $partialRefundItems->setPartialRefundItems($partialRefundItem);
        }

        return $partialRefundItems;
    }
}
