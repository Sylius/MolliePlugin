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

namespace Sylius\MolliePlugin\Calculator\Clearer;

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Model\AdjustmentInterface;

final class PaymentFeeAdjustmentClearer implements PaymentFeeAdjustmentClearerInterface
{
    public function clear(OrderInterface $order): void
    {
        $order->removeAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        $order->removeAdjustments(AdjustmentInterface::PERCENTAGE_ADJUSTMENT);
        $order->removeAdjustments(AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT);
    }
}
