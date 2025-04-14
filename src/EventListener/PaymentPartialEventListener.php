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

namespace Sylius\MolliePlugin\EventListener;

use Sylius\MolliePlugin\Exceptions\InvalidRefundAmountException;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefundInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class PaymentPartialEventListener
{
    public function __construct(
        private readonly OrderPaymentRefundInterface $orderPaymentRefund,
        private readonly MollieLoggerActionInterface $loggerAction,
    ) {
    }

    public function __invoke(UnitsRefunded $unitRefunded): void
    {
        try {
            $this->orderPaymentRefund->refund($unitRefunded);
        } catch (InvalidRefundAmountException $exception) {
            $this->loggerAction->addNegativeLog($exception->getMessage());
        } catch (HandlerFailedException $exception) {
            /** @var \Exception $previousException */
            $previousException = $exception->getPrevious();

            $this->loggerAction->addNegativeLog($previousException->getMessage());
        }
    }
}
