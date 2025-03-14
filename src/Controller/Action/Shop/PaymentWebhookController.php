<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\MolliePlugin\Controller\Action\Shop;

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
    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var MollieApiClientKeyResolverInterface */
    private $apiClientKeyResolver;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var PaymentRepositoryInterface */
    private $paymentRepository;

    /**
     * PaymentWebhookController constructor
     */
    public function __construct(
        MollieApiClient $mollieApiClient,
        MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        OrderRepositoryInterface $orderRepository,
        PaymentRepositoryInterface $paymentRepository,
    )
    {
        $this->mollieApiClient = $mollieApiClient;
        $this->apiClientKeyResolver = $apiClientKeyResolver;
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Mollie\Api\Exceptions\ApiException
     */
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
        switch ($molliePayment->status) {
            case PaymentStatus::STATUS_PENDING:
            case PaymentStatus::STATUS_OPEN:
                return PaymentInterface::STATE_PROCESSING;
            case PaymentStatus::STATUS_AUTHORIZED:
                return PaymentInterface::STATE_AUTHORIZED;
            case PaymentStatus::STATUS_PAID:
                return PaymentInterface::STATE_COMPLETED;
            case PaymentStatus::STATUS_CANCELED:
                return PaymentInterface::STATE_CANCELLED;
            case PaymentStatus::STATUS_EXPIRED:
            case PaymentStatus::STATUS_FAILED:
                return PaymentInterface::STATE_FAILED;
            default:
                return PaymentInterface::STATE_UNKNOWN;
        }
    }
}
