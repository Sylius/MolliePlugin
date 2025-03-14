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

namespace Sylius\MolliePlugin\Refund\Generator;

use Sylius\MolliePlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;

interface PaymentNewUnitRefundGeneratorInterface
{
    public function generate(OrderInterface $order, PartialRefundItems $partialRefundItems): PartialRefundItems;
}
