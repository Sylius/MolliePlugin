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

namespace Sylius\MolliePlugin\Cloner;

use Sylius\Component\Core\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class AdjustmentCloner implements AdjustmentClonerInterface
{
    public function __construct(private readonly FactoryInterface $adjustmentFactory)
    {
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
