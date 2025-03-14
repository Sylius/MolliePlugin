<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Applicator;

use Sylius\Component\Core\Model\OrderInterface;

interface UnitsPromotionAdjustmentsApplicatorInterface
{
    public function apply(OrderInterface $order, array $promotionAmount): void;
}
