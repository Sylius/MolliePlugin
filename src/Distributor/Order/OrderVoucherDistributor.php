<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Distributor\Order;

use Sylius\MolliePlugin\Applicator\UnitsPromotionAdjustmentsApplicatorInterface;
use Sylius\Component\Core\Distributor\ProportionalIntegerDistributorInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class OrderVoucherDistributor implements OrderVoucherDistributorInterface
{
    /** @var ProportionalIntegerDistributorInterface */
    private $proportionalIntegerDistributor;

    /** @var UnitsPromotionAdjustmentsApplicatorInterface */
    private $unitsPromotionAdjustmentsApplicator;

    public function __construct(
        ProportionalIntegerDistributorInterface $proportionalIntegerDistributor,
        UnitsPromotionAdjustmentsApplicatorInterface $unitsPromotionAdjustmentsApplicator
    ) {
        $this->proportionalIntegerDistributor = $proportionalIntegerDistributor;
        $this->unitsPromotionAdjustmentsApplicator = $unitsPromotionAdjustmentsApplicator;
    }

    public function distribute(OrderInterface $order, int $amount): void
    {
        $promotionAmount = $this->calculateAdjustmentAmount(
            $order->getPromotionSubjectTotal(),
            $amount
        );

        if (0 === $promotionAmount) {
            return;
        }

        $itemsTotals = [];
        foreach ($order->getItems() as $item) {
            $itemsTotals[] = $item->getTotal();
        }

        $splitPromotion = $this->proportionalIntegerDistributor->distribute($itemsTotals, $promotionAmount);
        $this->unitsPromotionAdjustmentsApplicator->apply($order, $splitPromotion);
    }

    private function calculateAdjustmentAmount(int $promotionSubjectTotal, int $targetPromotionAmount): int
    {
        return -1 * min($promotionSubjectTotal, $targetPromotionAmount);
    }
}
