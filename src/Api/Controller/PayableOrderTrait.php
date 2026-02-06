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

namespace Sylius\MolliePlugin\Api\Controller;

use Sylius\Component\Core\OrderPaymentStates;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait PayableOrderTrait
{
    protected function assertOrderIsPayable(OrderInterface $order): void
    {
        if (in_array($order->getPaymentState(), [
            OrderPaymentStates::STATE_PAID,
            OrderPaymentStates::STATE_CANCELLED,
            OrderPaymentStates::STATE_REFUNDED,
        ], true)) {
            throw new BadRequestHttpException(sprintf('Order with token "%s" cannot be paid.', $order->getTokenValue()));
        }
    }
}
