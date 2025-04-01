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

namespace Sylius\MolliePlugin\Refund\Creator;

use Mollie\Api\Resources\Order;
use Sylius\RefundPlugin\Command\RefundUnits;

interface OrderRefundCommandCreatorInterface
{
    public function fromOrder(Order $order): RefundUnits;
}
