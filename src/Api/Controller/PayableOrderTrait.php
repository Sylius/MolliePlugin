<?php

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
