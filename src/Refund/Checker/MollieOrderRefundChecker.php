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

namespace Sylius\MolliePlugin\Refund\Checker;

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
