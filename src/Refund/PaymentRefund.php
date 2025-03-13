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

namespace SyliusMolliePlugin\Refund;

use SyliusMolliePlugin\Creator\PaymentRefundCommandCreatorInterface;
use SyliusMolliePlugin\Exceptions\InvalidRefundAmountException;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use Mollie\Api\Resources\Payment;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class PaymentRefund implements PaymentRefundInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var PaymentRefundCommandCreatorInterface */
    private $commandCreator;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    public function __construct(
        MessageBusInterface $commandBus,
        PaymentRefundCommandCreatorInterface $commandCreator,
        MollieLoggerActionInterface $loggerAction
    ) {
        $this->commandBus = $commandBus;
        $this->commandCreator = $commandCreator;
        $this->loggerAction = $loggerAction;
    }

    public function refund(Payment $payment): void
    {
        try {
            $refundUnits = $this->commandCreator->fromPayment($payment);
            $this->commandBus->dispatch($refundUnits);
        } catch (InvalidRefundAmountException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());
        } catch (HandlerFailedException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());
        }
    }
}
