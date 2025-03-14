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

namespace Sylius\MolliePlugin\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Psr\Log\InvalidArgumentException;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\MolliePlugin\Action\Api\BaseApiAwareAction;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\MolliePlugin\Request\Api\CreateCustomer;
use Sylius\MolliePlugin\Request\Api\CreateInternalRecurring;
use Sylius\MolliePlugin\Request\Api\CreateOnDemandSubscription;
use Sylius\MolliePlugin\Request\Api\CreateOnDemandSubscriptionPayment;
use Sylius\MolliePlugin\Request\Api\CreateOrder;
use Sylius\MolliePlugin\Request\Api\CreatePayment;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;

final class CaptureAction extends BaseApiAwareAction implements CaptureActionInterface
{
    public const PAYMENT_FAILED_STATUS = 'failed';

    public const PAYMENT_CANCELLED_STATUS = 'cancelled';

    public const PAYMENT_NEW_STATUS = 'new';

    use GatewayAwareTrait;

    /** @var GenericTokenFactoryInterface|null */
    private $tokenFactory;

    public function __construct(private OrderRepositoryInterface $orderRepository, private MollieApiClientKeyResolverInterface $apiClientKeyResolver, private PaymentRepositoryInterface $paymentRepository)
    {
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
            if (isset($details['metadata']['methodType']) && Options::PAYMENT_API === $details['metadata']['methodType']) {
                if (in_array($details['metadata']['molliePaymentMethods'], Options::getOnlyOrderAPIMethods(), true)) {
                    throw new InvalidArgumentException(sprintf(
                        'Method %s is not allowed to use %s',
                        $details['metadata']['molliePaymentMethods'],
                        Options::PAYMENT_API,
                    ));
                }

                $this->gateway->execute(new CreatePayment($details));
            }

            if (isset($details['metadata']['methodType']) && Options::ORDER_API === $details['metadata']['methodType']) {
                if (in_array($details['metadata']['molliePaymentMethods'], Options::getOnlyPaymentAPIMethods(), true)) {
                    throw new InvalidArgumentException(sprintf(
                        'Method %s is not allowed to use %s',
                        $details['metadata']['molliePaymentMethods'],
                        Options::ORDER_API,
                    ));
                }

                $this->gateway->execute(new CreateOrder($details));
            }

            if (isset($details['metadata']['methodType']) && Options::ORDER_API === $details['metadata']['methodType']) {
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

    private function setQrCodeOnOrder(OrderInterface $order, ?string $qrCode = null)
    {
        try {
            $order->setQrCode($qrCode);
            $this->orderRepository->add($order);
        } catch (\Exception) {
        }
    }
}
