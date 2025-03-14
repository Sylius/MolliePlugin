<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Order;

use Sylius\Component\Core\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class AdjustmentCloner implements AdjustmentClonerInterface
{
    private FactoryInterface $adjustmentFactory;

    public function __construct(FactoryInterface $adjustmentFactory)
    {
        $this->adjustmentFactory = $adjustmentFactory;
    }

    public function clone(BaseAdjustmentInterface $adjustment): BaseAdjustmentInterface
    {
        /** @var BaseAdjustmentInterface $clonedAdjustment */
        $clonedAdjustment = $this->adjustmentFactory->createNew();
        $clonedAdjustment->setAmount($adjustment->getAmount());
        $clonedAdjustment->setType($adjustment->getType());
        $clonedAdjustment->setDetails($adjustment->getDetails());
        $clonedAdjustment->setLabel($adjustment->getLabel());
        $clonedAdjustment->setNeutral($adjustment->isNeutral());

        if ($adjustment->isLocked()) {
            $clonedAdjustment->lock();
        } else {
            $clonedAdjustment->unlock();
        }

        return $clonedAdjustment;
    }
}
