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

namespace Sylius\MolliePlugin\Controller\Shop;

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController
{
    public function __construct(
        private readonly MollieApiClient $mollieApiClient,
        private readonly MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ?PaymentRepositoryInterface $paymentRepository = null,
        private readonly ?MollieLoggerActionInterface $logger = null,
        private readonly ?StateMachineInterface $stateMachine = null,
        private readonly ?EntityManagerInterface $entityManager = null,
    ) {
        if (null === $this->logger) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '3.2',
                'Not passing MollieLoggerActionInterface to %s is deprecated and will not be supported in the future.',
                self::class,
            );
        }

        if (null === $this->stateMachine || null === $this->entityManager) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '3.3',
                'Not passing StateMachineInterface and EntityManagerInterface to %s is deprecated and will be required from 4.0. ' .
                'State changes currently fall back to direct Payment::setState() which bypasses state machine guards and after-callbacks (e.g. auto-creation of a new payment on fail/cancel).',
                self::class,
            );
        }

        if (null !== $this->paymentRepository) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '3.3',
                'Passing PaymentRepositoryInterface to %s is deprecated and will be removed in 4.0. ' .
                'It is only used by the setState() fallback path which is itself deprecated — prefer StateMachineInterface.',
                self::class,
            );
        }
    }

    /**
     * Every exit path returns 200 because Mollie keeps retrying the webhook on any
     * non-2xx response — "for any other response we keep trying"
     * (https://docs.mollie.com/docs/accepting-payments). When we cannot meaningfully
     * act (unknown Mollie id, unknown order, order has no payment, unsupported
     * status), a retry would not change the outcome, so we acknowledge and move on.
     */
    public function __invoke(Request $request): Response
    {
        $this->mollieApiClient->setApiKey($this->apiClientKeyResolver->getClientWithKey()->getApiKey());

        $molliePaymentId = $request->get('id');

        try {
            $molliePayment = $this->mollieApiClient->payments->get($molliePaymentId);
        } catch (ApiException $e) {
            $this->logger?->addLog(sprintf(
                'Mollie Webhook: Could not retrieve payment with id %s. Error: %s',
                $molliePaymentId,
                $e->getMessage(),
            ));

            return new JsonResponse(Response::HTTP_OK);
        }

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $request->get('orderId')]);
        if ($order === null) {
            return new JsonResponse(Response::HTTP_OK);
        }

        $payment = $order->getLastPayment();
        if (null === $payment) {
            return new JsonResponse(Response::HTTP_OK);
        }

        if (null !== $this->stateMachine && null !== $this->entityManager) {
            $this->applyTransition($payment, $molliePayment->status);
        } else {
            $this->applyLegacyState($payment, $molliePayment);
        }

        return new JsonResponse(Response::HTTP_OK);
    }

    private function applyTransition(PaymentInterface $payment, string $mollieStatus): void
    {
        $transition = $this->mapMolliePaymentStatusToTransition($mollieStatus);
        if (null === $transition) {
            return;
        }

        if (!$this->stateMachine->can($payment, PaymentTransitions::GRAPH, $transition)) {
            return;
        }

        $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, $transition);
        $this->entityManager->flush();
    }

    private function applyLegacyState(PaymentInterface $payment, Payment $molliePayment): void
    {
        $status = $this->mapMolliePaymentStatusToState($molliePayment);

        if ($payment->getState() !== $status && PaymentInterface::STATE_UNKNOWN !== $status) {
            $payment->setState($status);
            $this->paymentRepository->add($payment);
        }
    }

    private function mapMolliePaymentStatusToTransition(string $status): ?string
    {
        return match ($status) {
            PaymentStatus::STATUS_PENDING, PaymentStatus::STATUS_OPEN => PaymentTransitions::TRANSITION_PROCESS,
            PaymentStatus::STATUS_AUTHORIZED => PaymentTransitions::TRANSITION_AUTHORIZE,
            PaymentStatus::STATUS_PAID => PaymentTransitions::TRANSITION_COMPLETE,
            PaymentStatus::STATUS_CANCELED, PaymentStatus::STATUS_EXPIRED => PaymentTransitions::TRANSITION_CANCEL,
            PaymentStatus::STATUS_FAILED => PaymentTransitions::TRANSITION_FAIL,
            default => null,
        };
    }

    private function mapMolliePaymentStatusToState(Payment $molliePayment): string
    {
        return match ($molliePayment->status) {
            PaymentStatus::STATUS_PENDING, PaymentStatus::STATUS_OPEN => PaymentInterface::STATE_PROCESSING,
            PaymentStatus::STATUS_AUTHORIZED => PaymentInterface::STATE_AUTHORIZED,
            PaymentStatus::STATUS_PAID => PaymentInterface::STATE_COMPLETED,
            PaymentStatus::STATUS_CANCELED, PaymentStatus::STATUS_EXPIRED => PaymentInterface::STATE_CANCELLED,
            PaymentStatus::STATUS_FAILED => PaymentInterface::STATE_FAILED,
            default => PaymentInterface::STATE_UNKNOWN,
        };
    }
}
