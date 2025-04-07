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

namespace Sylius\MolliePlugin\Payum\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Psr\Log\InvalidArgumentException;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Model\ApiType;
use Sylius\MolliePlugin\Model\ApiTypeRestrictedPaymentMethods;
use Sylius\MolliePlugin\Payum\Request\CreateCustomer;
use Sylius\MolliePlugin\Payum\Request\CreateOrder;
use Sylius\MolliePlugin\Payum\Request\CreatePayment;
use Sylius\MolliePlugin\Payum\Request\Subscription\CreateInternalRecurring;
use Sylius\MolliePlugin\Payum\Request\Subscription\CreateOnDemandSubscription;
use Sylius\MolliePlugin\Payum\Request\Subscription\CreateOnDemandSubscriptionPayment;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;

final class CaptureAction extends BaseApiAwareAction implements GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    public const PAYMENT_FAILED_STATUS = 'failed';

    public const PAYMENT_CANCELLED_STATUS = 'cancelled';

    public const PAYMENT_NEW_STATUS = 'new';

    use GatewayAwareTrait;

    private ?GenericTokenFactoryInterface $tokenFactory = null;

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private PaymentRepositoryInterface $paymentRepository,
    ) {
    }

    public function setGenericTokenFactory(?GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    /** @param mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (true === isset($details['payment_mollie_id']) ||
            true === isset($details['subscription_mollie_id']) ||
            true === isset($details['order_mollie_id']) ||
            $request->getFirstModel()->getOrder()->getQrCode() ||
            $request->getFirstModel()->getOrder()->getMolliePaymentId()) {
            $qrCodeValue = $request->getFirstModel()->getOrder()->getQrCode();
            $molliePaymentId = $request->getFirstModel()->getOrder()->getMolliePaymentId();
            if ($qrCodeValue || $molliePaymentId) {
                $this->setQrCodeOnOrder($request->getFirstModel()->getOrder());
                $payment = $request->getFirstModel();

                if ($payment->getState() === self::PAYMENT_FAILED_STATUS ||
                    $payment->getState() === self::PAYMENT_CANCELLED_STATUS) {
                    $this->paymentRepository->add($this->createNewPayment($payment));
                }

                $this->mollieApiClient->setApiKey($this->apiClientKeyResolver->getClientWithKey()->getApiKey());
                $molliePayment = $this->mollieApiClient->payments->get($molliePaymentId);

                if (null !== $checkoutUrl = $molliePayment->getCheckoutUrl()) {
                    throw new HttpRedirect($checkoutUrl);
                }
            }

            return;
        }

        /** @var TokenInterface $token */
        $token = $request->getToken();

        if (null === $this->tokenFactory) {
            throw new RuntimeException();
        }

        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $refundToken = $this->tokenFactory->createRefundToken($token->getGatewayName(), $token->getDetails());

        $details['webhookUrl'] = $notifyToken->getTargetUrl();
        $details['backurl'] = $token->getTargetUrl();

        $metadata = $details['metadata'];
        $metadata['refund_token'] = $refundToken->getHash();
        $details['metadata'] = $metadata;

        if (true === $this->mollieApiClient->isRecurringSubscription()) {
            if ('first' === $details['sequenceType']) {
                $cancelToken = $this->tokenFactory->createToken(
                    $token->getGatewayName(),
                    $token->getDetails(),
                    'sylius_mollie_plugin_cancel_subscription_mollie',
                    ['orderId' => $details['metadata']['order_id']],
                );

                $details['cancel_token'] = $cancelToken->getHash();
                $this->gateway->execute(new CreateCustomer($details));
                $this->gateway->execute(new CreateInternalRecurring($details));
                $this->gateway->execute(new CreateOnDemandSubscription($details));
            } elseif ('recurring' === $details['sequenceType']) {
                $this->gateway->execute(new CreateOnDemandSubscriptionPayment($details));
            }
        } else {
            if (isset($details['metadata']['methodType']) && ApiType::PAYMENT_API === $details['metadata']['methodType']) {
                if (in_array($details['metadata']['molliePaymentMethods'], ApiTypeRestrictedPaymentMethods::onlyOrderApi(), true)) {
                    throw new InvalidArgumentException(sprintf(
                        'Method %s is not allowed to use %s',
                        $details['metadata']['molliePaymentMethods'],
                        ApiType::PAYMENT_API,
                    ));
                }

                $this->gateway->execute(new CreatePayment($details));
            }

            if (isset($details['metadata']['methodType']) && ApiType::ORDER_API === $details['metadata']['methodType']) {
                if (in_array($details['metadata']['molliePaymentMethods'], ApiTypeRestrictedPaymentMethods::onlyPaymentApi(), true)) {
                    throw new InvalidArgumentException(sprintf(
                        'Method %s is not allowed to use %s',
                        $details['metadata']['molliePaymentMethods'],
                        ApiType::ORDER_API,
                    ));
                }

                $this->gateway->execute(new CreateOrder($details));
            }

            if (isset($details['metadata']['methodType']) && ApiType::ORDER_API === $details['metadata']['methodType']) {
                $this->gateway->execute(new CreateOrder($details));
            }
        }
    }

    /** @param mixed $request */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }

    private function createNewPayment(PaymentInterface $payment): PaymentInterface
    {
        $newPayment = new Payment();
        $newPayment->setMethod($payment->getMethod());
        $newPayment->setOrder($payment->getOrder());
        $newPayment->setCurrencyCode($payment->getCurrencyCode());
        $newPayment->setAmount($payment->getAmount());
        $newPayment->setState(self::PAYMENT_NEW_STATUS);
        $newPayment->setDetails([]);
        $paymentDate = new \DateTime('now', $payment->getCreatedAt()->getTimezone());
        $newPayment->setCreatedAt($paymentDate);
        $newPayment->setUpdatedAt($paymentDate);

        return $newPayment;
    }

    private function setQrCodeOnOrder(OrderInterface $order, ?string $qrCode = null): void
    {
        try {
            $order->setQrCode($qrCode);
            $this->orderRepository->add($order);
        } catch (\Exception) {
        }
    }
}
