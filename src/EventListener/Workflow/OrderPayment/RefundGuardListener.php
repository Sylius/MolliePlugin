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

namespace Sylius\MolliePlugin\EventListener\Workflow\OrderPayment;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Refund\Guard\OrderPaymentRefundGuardInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

final class RefundGuardListener
{
    public function __construct(private OrderPaymentRefundGuardInterface $orderPaymentRefundGuard)
    {
    }

    public function __invoke(GuardEvent $event): void
    {
        $order = $event->getSubject();
        if (!$order instanceof OrderInterface) {
            return;
        }

        $event->setBlocked(!$this->orderPaymentRefundGuard->isRefundPossible($order));
    }
}
