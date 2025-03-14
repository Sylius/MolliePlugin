<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Refund;

use Mollie\Api\Resources\Order;

interface OrderRefundInterface
{
    public function refund(Order $order): void;
}
