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

namespace Sylius\MolliePlugin\Refund;

use Mollie\Api\Resources\Payment;
use Sylius\MolliePlugin\Exceptions\InvalidRefundAmountException;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Refund\Creator\PaymentRefundCommandCreatorInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class PaymentRefund implements PaymentRefundInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly PaymentRefundCommandCreatorInterface $commandCreator,
        private readonly MollieLoggerActionInterface $loggerAction,
    ) {
    }

    public function refund(Payment $payment): void
    {
        try {
            $refundUnits = $this->commandCreator->fromPayment($payment);
            $this->commandBus->dispatch($refundUnits);
        } catch (HandlerFailedException|InvalidRefundAmountException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());
        }
    }
}
