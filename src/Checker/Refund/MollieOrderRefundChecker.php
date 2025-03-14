<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Checker\Refund;

use Mollie\Api\Resources\Order;
use Mollie\Api\Resources\OrderLine;

final class MollieOrderRefundChecker implements MollieOrderRefundCheckerInterface
{
    public function check(Order $order): bool
    {
        /** @var OrderLine $line */
        foreach ($order->lines as $line) {
            if (0 < $line->quantityRefunded) {
                return true;
            }
        }

        return false;
    }
}
