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

use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Exceptions\InvalidRefundAmountException;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefundInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Sylius\RefundPlugin\Entity\RefundInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class PaymentPartialEventListener
{
    public function __construct(
        private readonly OrderPaymentRefundInterface $orderPaymentRefund,
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly RepositoryInterface $refundRepository,
    ) {
    }

    public function __invoke(UnitsRefunded $unitRefunded): void
    {
        $order = $this->orderRepository->findOneBy(['number' => $unitRefunded->orderNumber()]);
        if (null !== $order) {
            /** @var RefundInterface[] $existing */
            $existing = $this->refundRepository->findBy(['order' => $order->getId()]);
            $alreadyRefunded = 0;
            foreach ($existing as $refund) {
                $alreadyRefunded += $refund->getAmount();
            }

            $requested = $unitRefunded->amount();

            if ($requested <= $alreadyRefunded) {
                $this->loggerAction->addLog('Skipping refund: already processed for this order and amount');

                return;
            }
        }

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
