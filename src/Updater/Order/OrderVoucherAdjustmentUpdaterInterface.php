<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Updater\Order;

use Mollie\Api\Resources\Payment;

interface OrderVoucherAdjustmentUpdaterInterface
{
    public function update(Payment $molliePayment, int $orderId): void;
}
