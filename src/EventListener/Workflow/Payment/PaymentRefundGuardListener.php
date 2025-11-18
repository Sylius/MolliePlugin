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

namespace Sylius\MolliePlugin\EventListener\Workflow\Payment;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Refund\Guard\PaymentRefundGuardInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

final class PaymentRefundGuardListener
{
    public function __construct(private PaymentRefundGuardInterface $paymentRefundGuard)
    {
    }

    public function __invoke(GuardEvent $event): void
    {
        $payment = $event->getSubject();
        if (!$payment instanceof PaymentInterface) {
            return;
        }

        $event->setBlocked(!$this->paymentRefundGuard->isRefundPossible($payment));
    }
}
