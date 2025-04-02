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

namespace Sylius\MolliePlugin\ApplePay\Resolver;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Api\Types\PaymentStatus;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProvider;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Model\ApiType;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;

final class ApplePayDirectApiPaymentResolver implements ApplePayDirectApiPaymentResolverInterface
{
    public function __construct(
        private readonly MollieApiClient $mollieApiClient,
        private readonly MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private readonly OrderPaymentApplePayDirectProvider $paymentApplePayDirectProvider,
    ) {
    }

    public function resolve(OrderInterface $order, array $details): void
    {
        $this->apiClientKeyResolver->getClientWithKey();
        $customer = $order->getCustomer();

        /** @var PaymentInterface $payment */
        $payment = $order->getLastPayment();
        $refundToken = $payment->getDetails()['refund_token'];

        $metadata = [
            'order_id' => $order->getId(),
            'customer_id' => null !== $customer ? $customer->getId() : null,
            'molliePaymentMethods' => PaymentMethod::APPLEPAY,
            'methodType' => ApiType::PAYMENT_API,
            'refund_token' => $refundToken,
        ];

        try {
            $response = $this->mollieApiClient->payments->create(
                [
                    'method' => PaymentMethod::APPLEPAY,
                    'amount' => $details['amount'],
                    'description' => 'Apple pay direct payment',
                    'applePayPaymentToken' => $details['applePayDirectToken'],
                    'metadata' => $metadata,
                    'redirectUrl' => $details['backurl'],
                ],
            );

            if (PaymentStatus::STATUS_PAID === $response->status) {
                $this->paymentApplePayDirectProvider->applyRequiredTransition($payment, PaymentInterface::STATE_COMPLETED);

                $paymentDetails = $payment->getDetails();
                $paymentDetails['payment_mollie_id'] = $response->id;
                $paymentDetails['metadata'] = $metadata;
                $payment->setDetails($paymentDetails);
            }
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage());
        }
    }
}
