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

namespace Sylius\MolliePlugin\Checker\Refund;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Repository\CreditMemoRepositoryInterface;
use Sylius\MolliePlugin\Repository\OrderRepositoryInterface;
use Sylius\RefundPlugin\Command\RefundUnits;

final class DuplicateRefundTheSameAmountChecker implements DuplicateRefundTheSameAmountCheckerInterface
{
    public function __construct(private readonly CreditMemoRepositoryInterface $creditMemoRepository, private readonly OrderRepositoryInterface $orderRepository)
    {
    }

    public function check(RefundUnits $command): bool
    {
        $dateTimeInterval = new \DateInterval(self::ONE_HOUR_INTERVAL);
        $now = new \DateTime('now');
        $now->sub($dateTimeInterval);

        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['number' => $command->orderNumber()]);

        $creditMemos = $this->creditMemoRepository->findByOrderNumberAndDateTime(
            $order->getId(),
            $now,
            $this->getTotalAmount($command),
        );

        return 0 !== count($creditMemos);
    }

    private function getTotalAmount(RefundUnits $command): int
    {
        $total = 0;

        foreach ($command->units() as $unit) {
            $total += $unit->total();
        }

        foreach ($command->shipments() as $shipment) {
            $total += $shipment->total();
        }

        return $total;
    }
}
