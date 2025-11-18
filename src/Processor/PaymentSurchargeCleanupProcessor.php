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

namespace Sylius\MolliePlugin\Processor;

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\MolliePlugin\Calculator\Clearer\PaymentFeeAdjustmentClearerInterface;

final readonly class PaymentSurchargeCleanupProcessor implements OrderProcessorInterface
{
    public function __construct(
        private PaymentFeeAdjustmentClearerInterface $paymentFeeAdjustmentClearer,
    ) {
    }

    public function process(OrderInterface $order): void
    {
        if (!$order->canBeProcessed()) {
            return;
        }

        $this->paymentFeeAdjustmentClearer->clear($order);
    }
}
