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

use Mollie\Api\MollieApiClient;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Exceptions\InvalidRefundAmountException;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefundInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Sylius\RefundPlugin\Entity\RefundInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Sylius\RefundPlugin\ProcessManager\UnitsRefundedProcessStepInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class PaymentPartialEventListener implements UnitsRefundedProcessStepInterface
{
    public function __construct(
        private readonly OrderPaymentRefundInterface $orderPaymentRefund,
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly MollieApiClientKeyResolverInterface $mollieApiClientResolver,
        private readonly DivisorProviderInterface $divisorProvider,
        private readonly RepositoryInterface $refundRepository,
    ) {
    }

    public function next(UnitsRefunded $unitsRefunded): void
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['number' => $unitsRefunded->orderNumber()]);
        if (null === $order) {
            return;
        }

        /** @var PaymentInterface|false $payment */
        $payment = $order->getPayments()->last();
        if (!$payment instanceof PaymentInterface) {
            return;
        }

        $paymentMollieId = $payment->getDetails()['payment_mollie_id'] ?? null;
        if (null === $paymentMollieId) {
            return;
        }

        $client = $this->mollieApiClientResolver->getClientWithKey($order);

        $mollieRefundedAmount = $this->getMollieRefundedAmount($client, $paymentMollieId);
        $syliusRefundedAmount = $this->getSyliusRefundedAmount($order);

        if ($mollieRefundedAmount >= $syliusRefundedAmount) {
            $this->loggerAction->addLog(
                sprintf(
                    'Skipping Mollie API call: refund already exists on Mollie (Mollie: %d, Sylius: %d)',
                    $mollieRefundedAmount,
                    $syliusRefundedAmount,
                ),
            );

            return;
        }

        try {
            $this->orderPaymentRefund->refund($unitsRefunded);
        } catch (InvalidRefundAmountException $exception) {
            $this->loggerAction->addNegativeLog($exception->getMessage());
        } catch (HandlerFailedException $exception) {
            $previousException = $exception->getPrevious();
            $this->loggerAction->addNegativeLog($previousException?->getMessage() ?? $exception->getMessage());
        }
    }

    private function getMollieRefundedAmount(MollieApiClient $client, string $paymentMollieId): int
    {
        try {
            $molliePayment = $client->payments->get($paymentMollieId);

            if (null === $molliePayment->amountRefunded) {
                return 0;
            }

            return (int) ((float) $molliePayment->amountRefunded->value * $this->divisorProvider->getDivisor());
        } catch (\Exception $e) {
            $this->loggerAction->addNegativeLog(
                sprintf('Failed to get Mollie payment %s: %s', $paymentMollieId, $e->getMessage()),
            );

            return 0;
        }
    }

    /** The return value already includes the refund currently being processed */
    private function getSyliusRefundedAmount(OrderInterface $order): int
    {
        /** @var RefundInterface[] $refunds */
        $refunds = $this->refundRepository->findBy(['order' => $order->getId()]);

        $total = 0;
        foreach ($refunds as $refund) {
            $total += $refund->getAmount();
        }

        return $total;
    }
}
