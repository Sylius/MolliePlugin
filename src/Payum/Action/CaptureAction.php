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

use Mollie\Api\Resources\Order as MollieOrder;
use Mollie\Api\Resources\Payment as MolliePayment;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
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

    private const TERMINAL_MOLLIE_STATUSES = [
        PaymentStatus::STATUS_CANCELED,
        PaymentStatus::STATUS_FAILED,
        PaymentStatus::STATUS_EXPIRED,
    ];

    private const OPEN_MOLLIE_STATUSES = [
        PaymentStatus::STATUS_OPEN,
        OrderStatus::STATUS_CREATED,
    ];

    private const PENDING_MOLLIE_STATUSES = [
        PaymentStatus::STATUS_PENDING,
    ];

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

        if (true === isset($details['subscription_mollie_id'])) {
            return;
        }

        if ($request->getFirstModel()->getOrder()->getQrCode() ||
            $request->getFirstModel()->getOrder()->getMolliePaymentId()) {
            $this->handleQrCodeOrApplePay($request, $details);

            return;
        }

        if (isset($details['payment_mollie_id']) || isset($details['order_mollie_id'])) {
            $handled = $this->handleExistingMolliePayment($request, $details);

            if ($handled) {
                return;
            }
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
        $this->rememberCaptureTokenHash($details, $token->getHash());

        $metadata = $details['metadata'];
        $metadata['refund_token'] = $refundToken->getHash();
        $details['metadata'] = $metadata;

        if (true === $this->mollieApiClient->isRecurringSubscription()) {
            if ('first' === $details['sequenceType']) {
                $cancelToken = $this->tokenFactory->createToken(
                    $token->getGatewayName(),
                    $token->getDetails(),
                    'sylius_mollie_shop_cancel_subscription_mollie',
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

    /**
     * True = caller returns; false = caller creates a fresh Mollie payment.
     */
    private function handleExistingMolliePayment(Capture $request, ArrayObject $details): bool
    {
        $paymentMollieId = $details['payment_mollie_id'] ?? null;
        $orderMollieId = $details['order_mollie_id'] ?? null;

        try {
            $mollieResource = null !== $paymentMollieId
                ? $this->mollieApiClient->payments->get($paymentMollieId)
                : $this->mollieApiClient->orders->get($orderMollieId, ['embed' => 'payments']);
        } catch (\Exception) {
            // Can't reach Mollie — let Payum's normal Status flow try next.
            return true;
        }

        $mollieStatus = $mollieResource->status;

        // Terminal (canceled/failed/expired): let Payum's Status flow transition the
        // payment so `sylius_process_order` creates a fresh Payment for the next retry.
        // Creating a new Mollie payment here would race Payum's Status flow and, when
        // Mollie's redirectUrl points back to the capture endpoint, produce a redirect
        // loop.
        if (in_array($mollieStatus, self::TERMINAL_MOLLIE_STATUSES, true)) {
            return true;
        }

        if (in_array($mollieStatus, self::OPEN_MOLLIE_STATUSES, true)) {
            if (in_array($request->getToken()->getHash(), $details['capture_token_hashes'] ?? [], true)) {
                return true;
            }

            // Always create a fresh session — reusing would return to a stale Payum
            // token. Best-effort cancel; for non-cancelable methods the old session
            // stays orphaned until Mollie expires it (see #329).
            if (true === ($mollieResource->isCancelable ?? false)) {
                try {
                    $this->cancelMollieResource($mollieResource);
                } catch (\Exception) {
                }
            }

            $this->appendToMollieIdHistory($details, $paymentMollieId ?? $orderMollieId);
            $this->rebuildDetailsForRetry($request, $details);

            return false;
        }

        if (in_array($mollieStatus, self::PENDING_MOLLIE_STATUSES, true)) {
            // Customer is mid-payment on the PSP side — don't spawn a competing session.
            return true;
        }

        // paid/authorized — let Payum status flow complete.
        return true;
    }

    /**
     * Rebuild details via Convert on retry. Payum's CapturePaymentAction only runs
     * Convert when GetHumanStatus is "new"; stale payment_mollie_id in details means
     * it isn't, so Convert is skipped and details lack amount/description/metadata
     * that CreatePayment needs. Dispatch Convert explicitly to fill them in.
     */
    private function rebuildDetailsForRetry(Capture $request, ArrayObject $details): void
    {
        $firstModel = $request->getFirstModel();

        if (!$firstModel instanceof PaymentInterface) {
            $this->clearStaleDetailsForRetry($details);

            return;
        }

        $history = $details['mollie_payment_ids_history'] ?? [];
        $tokenHashes = $details['capture_token_hashes'] ?? [];

        try {
            $convert = new Convert($firstModel, 'array', $request->getToken());
            $this->gateway->execute($convert);
            $result = $convert->getResult();
        } catch (\Exception) {
            $this->clearStaleDetailsForRetry($details);

            return;
        }

        if (!is_array($result)) {
            $this->clearStaleDetailsForRetry($details);

            return;
        }

        foreach (array_keys($details->getArrayCopy()) as $key) {
            unset($details[$key]);
        }

        foreach ($result as $key => $value) {
            $details[$key] = $value;
        }

        if ([] !== $history) {
            $details['mollie_payment_ids_history'] = $history;
        }

        if ([] !== $tokenHashes) {
            $details['capture_token_hashes'] = $tokenHashes;
        }

        $firstModel->setDetails((array) $details);
    }

    private function appendToMollieIdHistory(ArrayObject $details, ?string $supersededId): void
    {
        if (null === $supersededId) {
            return;
        }

        $history = $details['mollie_payment_ids_history'] ?? [];

        if (!in_array($supersededId, $history, true)) {
            $history[] = $supersededId;
        }

        $details['mollie_payment_ids_history'] = $history;
    }

    private function rememberCaptureTokenHash(ArrayObject $details, string $tokenHash): void
    {
        $hashes = $details['capture_token_hashes'] ?? [];

        if (!in_array($tokenHash, $hashes, true)) {
            $hashes[] = $tokenHash;
        }

        $details['capture_token_hashes'] = $hashes;
    }

    /**
     * Payment resource has no instance-level cancel(); Order does. Route accordingly.
     */
    private function cancelMollieResource(MollieOrder|MolliePayment $resource): void
    {
        if ($resource instanceof MollieOrder) {
            $resource->cancel();

            return;
        }

        $this->mollieApiClient->payments->cancel($resource->id);
    }

    private function clearStaleDetailsForRetry(ArrayObject $details): void
    {
        $newMethod = $details['molliePaymentMethods'] ?? null;

        unset(
            $details['payment_mollie_id'],
            $details['order_mollie_id'],
            $details['webhookUrl'],
            $details['backurl'],
        );

        if (null !== $newMethod && isset($details['metadata'])) {
            $metadata = $details['metadata'];
            $metadata['molliePaymentMethods'] = $newMethod;
            unset($metadata['refund_token']);
            $details['metadata'] = $metadata;

            return;
        }

        // API-flow (SelectMollieMethodAction) stores the payment config as flat keys
        // and never populates `metadata`. On UI retry the rest of this action reads
        // `metadata.methodType`/`molliePaymentMethods` to decide which Mollie endpoint
        // to call. Normalize the flat shape into the nested one expected by the UI
        // flow so retry can dispatch CreatePayment correctly.
        if (!isset($details['metadata']) && null !== $newMethod) {
            $details['metadata'] = [
                'molliePaymentMethods' => $newMethod,
                'cartToken' => $details['cartToken'] ?? null,
                'saveCardInfo' => $details['saveCardInfo'] ?? null,
                'useSavedCards' => $details['useSavedCards'] ?? null,
                'methodType' => ApiType::PAYMENT_API,
            ];
        }
    }

    private function handleQrCodeOrApplePay(Capture $request, ArrayObject $details): void
    {
        $qrCodeValue = $request->getFirstModel()->getOrder()->getQrCode();
        $molliePaymentId = $request->getFirstModel()->getOrder()->getMolliePaymentId();

        if (!$qrCodeValue && !$molliePaymentId) {
            return;
        }

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
