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

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\OrderInterface;
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
        private readonly PaymentRepositoryInterface $paymentRepository,
    ) {
    }

    /** @throws ApiException */
    public function __invoke(Request $request): Response
    {
        $this->mollieApiClient->setApiKey($this->apiClientKeyResolver->getClientWithKey()->getApiKey());
        $molliePayment = $this->mollieApiClient->payments->get($request->get('id'));

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $request->get('orderId')]);
        if ($order === null) {
            return new JsonResponse(Response::HTTP_OK);
        }

        $payment = $order->getLastPayment();
        $status = $this->getStatus($molliePayment);

        if ($payment->getState() !== $status && PaymentInterface::STATE_UNKNOWN !== $status) {
            $payment->setState($status);
            $this->paymentRepository->add($payment);
        }

        return new JsonResponse(Response::HTTP_OK);
    }

    private function getStatus(Payment $molliePayment): string
    {
        return match ($molliePayment->status) {
            PaymentStatus::STATUS_PENDING, PaymentStatus::STATUS_OPEN => PaymentInterface::STATE_PROCESSING,
            PaymentStatus::STATUS_AUTHORIZED => PaymentInterface::STATE_AUTHORIZED,
            PaymentStatus::STATUS_PAID => PaymentInterface::STATE_COMPLETED,
            PaymentStatus::STATUS_CANCELED => PaymentInterface::STATE_CANCELLED,
            PaymentStatus::STATUS_EXPIRED, PaymentStatus::STATUS_FAILED => PaymentInterface::STATE_FAILED,
            default => PaymentInterface::STATE_UNKNOWN,
        };
    }
}
