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

namespace SyliusMolliePlugin\Checker\Refund;

use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Repository\CreditMemoRepositoryInterface;
use SyliusMolliePlugin\Repository\OrderRepositoryInterface;
use Sylius\RefundPlugin\Command\RefundUnits;

final class DuplicateRefundTheSameAmountChecker implements DuplicateRefundTheSameAmountCheckerInterface
{
    /** @var CreditMemoRepositoryInterface */
    private $creditMemoRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(
        CreditMemoRepositoryInterface $creditMemoRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->creditMemoRepository = $creditMemoRepository;
        $this->orderRepository = $orderRepository;
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
            $this->getTotalAmount($command)
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
