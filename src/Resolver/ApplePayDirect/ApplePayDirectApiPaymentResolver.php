<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\ApplePayDirect;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\MolliePlugin\Provider\Order\OrderPaymentApplePayDirectProvider;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Api\Types\PaymentStatus;
use Sylius\Component\Core\Model\PaymentInterface;

final class ApplePayDirectApiPaymentResolver implements ApplePayDirectApiPaymentResolverInterface
{
    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var MollieApiClientKeyResolverInterface */
    private $apiClientKeyResolver;

    /** @var OrderPaymentApplePayDirectProvider */
    private $paymentApplePayDirectProvider;

    public function __construct(
        MollieApiClient $mollieApiClient,
        MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        OrderPaymentApplePayDirectProvider $paymentApplePayDirectProvider
    ) {
        $this->mollieApiClient = $mollieApiClient;
        $this->apiClientKeyResolver = $apiClientKeyResolver;
        $this->paymentApplePayDirectProvider = $paymentApplePayDirectProvider;
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
            'methodType' => Options::PAYMENT_API,
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
                ]
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
