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

namespace Sylius\MolliePlugin\Resolver\ApplePayDirect;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Helper\ConvertOrderInterface;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\Order\OrderPaymentApplePayDirectProvider;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Sylius\MolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentMethod;
use Sylius\Component\Core\Model\PaymentInterface;

final class ApplePayDirectApiOrderPaymentResolver implements ApplePayDirectApiOrderPaymentResolverInterface
{
    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var MollieApiClientKeyResolverInterface */
    private $apiClientKeyResolver;

    /** @var ConvertOrderInterface */
    private $convertOrder;

    /** @var OrderPaymentApplePayDirectProvider */
    private $paymentApplePayDirectProvider;

    /** @var PaymentLocaleResolverInterface */
    private $paymentLocaleResolver;

    /** @var DivisorProviderInterface */
    private $divisorProvider;

    public function __construct(
        MollieApiClient $mollieApiClient,
        MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        ConvertOrderInterface $convertOrder,
        OrderPaymentApplePayDirectProvider $paymentApplePayDirectProvider,
        PaymentLocaleResolverInterface $paymentLocaleResolver,
        DivisorProviderInterface $divisorProvider
    ) {
        $this->mollieApiClient = $mollieApiClient;
        $this->apiClientKeyResolver = $apiClientKeyResolver;
        $this->convertOrder = $convertOrder;
        $this->paymentApplePayDirectProvider = $paymentApplePayDirectProvider;
        $this->paymentLocaleResolver = $paymentLocaleResolver;
        $this->divisorProvider = $divisorProvider;
    }

    public function resolve(
        OrderInterface $order,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        array $details
    ): void {
        $this->apiClientKeyResolver->getClientWithKey();
        $details = $this->convertOrder->convert($order, $details, $this->divisorProvider->getDivisor(), $mollieGatewayConfig);
        $customer = $order->getCustomer();

        $orderExpiredTime = $mollieGatewayConfig->getOrderExpiration();
        if (null !== $orderExpiredTime) {
            $interval = new \DateInterval('P' . $orderExpiredTime . 'D');
            $dateExpired = new \DateTimeImmutable('now');
            $dateExpired = $dateExpired->add($interval);
        }

        /** @var PaymentInterface $payment */
        $payment = $order->getLastPayment();
        $refundToken = $payment->getDetails()['refund_token'];

        $metadata = [
            'order_id' => $order->getId(),
            'customer_id' => null !== $customer ? $customer->getId() : null,
            'molliePaymentMethods' => PaymentMethod::APPLEPAY,
            'refund_token' => $refundToken,
            'methodType' => Options::ORDER_API,
        ];

        try {
            $requestData = [
                'method' => PaymentMethod::APPLEPAY,
                'payment' => [
                    'applePayPaymentToken' => $details['applePayDirectToken'],
                ],
                'amount' => $details['amount'],
                'billingAddress' => $details['billingAddress'],
                'shippingAddress' => $details['shippingAddress'],
                'orderNumber' => $details['orderNumber'],
                'redirectUrl' => $details['backurl'],
                'lines' => $details['lines'],
                'metadata' => $metadata,
                'expiresAt' => isset($dateExpired) ?
                    $dateExpired->format('Y-m-d') :
                    (new \DateTimeImmutable('now'))->format('Y-m-d'),
            ];

            if (null !== ($paymentLocale = $this->paymentLocaleResolver->resolveFromOrder($order))) {
                $requestData['locale'] = $paymentLocale;
            }

            $response = $this->mollieApiClient->orders->create($requestData);
            if (OrderStatus::STATUS_PAID === $response->status) {
                $this->paymentApplePayDirectProvider->applyRequiredTransition($payment, PaymentInterface::STATE_COMPLETED);

                $paymentDetails = $payment->getDetails();
                $paymentDetails['order_mollie_id'] = $response->id;
                $paymentDetails['metadata'] = $metadata;
                $payment->setDetails($paymentDetails);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage());
        }
    }
}
