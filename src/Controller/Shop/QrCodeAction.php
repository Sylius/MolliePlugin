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

use Mollie\Api\Resources\Payment;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Converter\IntToStringConverterInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Model\ApiType;
use Sylius\MolliePlugin\Model\DTO\MolliePayment\Amount;
use Sylius\MolliePlugin\Model\DTO\MolliePayment\Metadata;
use Sylius\MolliePlugin\Model\DTO\MolliePayment\MolliePayment;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class QrCodeAction
{
    public function __construct(
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly CartContextInterface $cartContext,
        private readonly MollieApiClient $mollieApiClient,
        private readonly MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RepositoryInterface $methodRepository,
        private readonly IntToStringConverterInterface $intToStringConverter,
    ) {
    }

    public function createPayment(Request $request): Response
    {
        /** @var MollieGatewayConfigInterface $method */
        $method = $this->methodRepository->findOneBy(['methodId' => $request->get('paymentMethod')]);
        $qrCodeEnabled = $method->isQrCodeEnabled();

        if ($qrCodeEnabled) {
            /** @var OrderInterface $order */
            $order = $this->cartContext->getCart();
            $molliePayment = $this->buildPaymentObject($request, $order);

            try {
                $this->mollieApiClient->setApiKey($this->apiClientKeyResolver->getClientWithKey()->getApiKey());
                /** @var Payment $payment */
                $payment = $this->mollieApiClient->payments->create($molliePayment->toArray(), ['include' => 'details.qrCode']);

                // save qr code to the db order
                $qrCodeObject = $payment->details->qrCode;
                $this->setQrCodeOnOrder($order, $qrCodeObject->src);
                $this->setMolliePaymentIdOnOrder($order, $payment->id);

                return new JsonResponse(['qrCode' => $qrCodeObject], Response::HTTP_OK);
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(sprintf('Error with payment creation: %s', $e->getMessage()));
            }
        }

        return new JsonResponse(['qrCode' => null], Response::HTTP_OK);
    }

    public function fetchQrCodeFromOrder(Request $request): JsonResponse
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();
        $qrCode = null;
        $orderId = $request->get('orderId');

        if (null !== $orderId) {
            /** @var OrderInterface|null $order */
            $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        }

        if (null !== $order) {
            $qrCode = $order->getQrCode();
        }

        return new JsonResponse(['qrCode' => $qrCode, 'orderId' => $order->getId()], Response::HTTP_OK);
    }

    public function removeQrCodeFromOrder(Request $request): JsonResponse
    {
        $shouldDeletePaymentId = (bool) $request->get('shouldDeletePaymentId');

        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();
        $orderToken = $request->get('orderToken');
        if (null !== $orderToken) {
            /** @var OrderInterface|null $order */
            $order = $this->orderRepository->findOneByTokenValue($orderToken);
        }
        $this->setQrCodeOnOrder($order, null, $shouldDeletePaymentId);

        return new JsonResponse(['status' => Response::HTTP_OK]);
    }

    private function setQrCodeOnOrder(
        OrderInterface $order,
        ?string $qrCode = null,
        bool $shouldDeletePaymentId = false,
    ): void {
        try {
            $order->setQrCode($qrCode);
            if ($shouldDeletePaymentId) {
                $order->setMolliePaymentId(null);
            }
            $this->orderRepository->add($order);
        } catch (\Exception $exception) {
            $this->loggerAction->addNegativeLog(sprintf('Could not update qr code url on order: %s', $exception->getMessage()));
        }
    }

    private function setMolliePaymentIdOnOrder(OrderInterface $order, ?string $molliePaymentId = null): void
    {
        try {
            $order->setMolliePaymentId($molliePaymentId);
            $this->orderRepository->add($order);
        } catch (\Exception $exception) {
            $this->loggerAction->addNegativeLog(sprintf('Could not update mollie payment id on order: %s', $exception->getMessage()));
        }
    }

    /**
     * Create mollie payment dto
     */
    private function buildPaymentObject(Request $request, OrderInterface $order): MolliePayment
    {
        $molliePayment = new MolliePayment();
        $molliePayment->setAmount(new Amount($this->intToStringConverter->convertIntToString($order->getTotal()), $order->getCurrencyCode()));
        $molliePayment->setMethod($request->get('paymentMethod'));
        $molliePayment->setDescription((string) $order->getId());
        $molliePayment->setIssuer($request->get('issuer') ?? '');
        $redirectUrl = $this->urlGenerator->generate('sylius_mollie_plugin_payum', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $webhookUrl = $this->urlGenerator->generate('sylius_mollie_plugin_payment_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $redirectUrl .= '?orderId=' . $order->getId();
        $webhookUrl .= '?orderId=' . $order->getId();
        $molliePayment->setWebhookUrl($webhookUrl);
        $molliePayment->setRedirectUrl($redirectUrl);

        $metadata = new Metadata(
            $order->getId(),
            (string) $order->getCustomer()->getId(),
            $request->get('paymentMethod'),
            null,
            null,
            null,
            $request->get('issuer') ?? '',
            ApiType::PAYMENT_API,
        );
        $molliePayment->setMetadata($metadata);
        $molliePayment->setCustomerId(null);
        $molliePayment->setLocale($order->getLocaleCode());

        return $molliePayment;
    }
}
