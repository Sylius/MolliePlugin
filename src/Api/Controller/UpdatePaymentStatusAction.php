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

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Types\PaymentStatus as MolliePaymentStatus;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdatePaymentStatusAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MollieApiClientKeyResolverInterface $apiKeyResolver,
        private readonly StateMachineInterface $stateMachine,
        private readonly MollieLoggerActionInterface $logger,
    ) {
    }

    public function __invoke(string $tokenValue): JsonResponse
    {
        $order = $this->orderRepository->findOneByTokenValue($tokenValue);
        if (!$order instanceof OrderInterface) {
            throw new NotFoundHttpException(sprintf('Order with token "%s" not found', $tokenValue));
        }

        $payment = $order->getLastPayment();
        if (null === $payment) {
            throw new NotFoundHttpException('No payment found for this order');
        }

        $details = $payment->getDetails();
        $molliePaymentId = $details['payment_mollie_id'] ?? null;
        if (null === $molliePaymentId) {
            $this->logger->addNegativeLog(sprintf(
                'No Mollie payment ID found in payment details of order with token: %s',
                $tokenValue,
            ));

            throw new NotFoundHttpException('No payment found for this order');
        }

        try {
            $mollieApiClient = $this->apiKeyResolver->getClientWithKey($order);
            $molliePayment = $mollieApiClient->payments->get($molliePaymentId);
            $mollieStatus = $molliePayment->status;
        } catch (\Exception $exception) {
            $this->logger->addNegativeLog(sprintf('Error fetching Mollie payment status: %s', $exception->getMessage()));

            return new JsonResponse([
                'error' => 'Could not retrieve Mollie payment status',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $transition = $this->mapMolliePaymentStatusToTransition($mollieStatus);

        if (null !== $transition && $this->stateMachine->can($payment, PaymentTransitions::GRAPH, $transition)) {
            $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, $transition);
            $this->entityManager->flush();
        }

        return new JsonResponse([
            'paymentState' => $payment->getState(),
        ]);
    }

    private function mapMolliePaymentStatusToTransition(string $status): ?string
    {
        return match ($status) {
            MolliePaymentStatus::STATUS_PENDING, MolliePaymentStatus::STATUS_OPEN => PaymentTransitions::TRANSITION_PROCESS,
            MolliePaymentStatus::STATUS_AUTHORIZED => PaymentTransitions::TRANSITION_AUTHORIZE,
            MolliePaymentStatus::STATUS_PAID => PaymentTransitions::TRANSITION_COMPLETE,
            MolliePaymentStatus::STATUS_CANCELED => PaymentTransitions::TRANSITION_CANCEL,
            MolliePaymentStatus::STATUS_EXPIRED, MolliePaymentStatus::STATUS_FAILED => PaymentTransitions::TRANSITION_FAIL,
            default => null,
        };
    }
}
