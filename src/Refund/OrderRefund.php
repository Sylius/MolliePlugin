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

use Sylius\MolliePlugin\Creator\OrderRefundCommandCreatorInterface;
use Sylius\MolliePlugin\Exceptions\InvalidRefundAmountException;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Mollie\Api\Resources\Order;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class OrderRefund implements OrderRefundInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var OrderRefundCommandCreatorInterface */
    private $commandCreator;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    public function __construct(
        MessageBusInterface $commandBus,
        OrderRefundCommandCreatorInterface $commandCreator,
        MollieLoggerActionInterface $loggerAction
    ) {
        $this->commandBus = $commandBus;
        $this->commandCreator = $commandCreator;
        $this->loggerAction = $loggerAction;
    }

    public function refund(Order $order): void
    {
        try {
            $refundUnits = $this->commandCreator->fromOrder($order);
            $this->commandBus->dispatch($refundUnits);
        } catch (InvalidRefundAmountException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());
        } catch (HandlerFailedException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());
        }
    }
}
