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

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\MealVoucher;
use Sylius\MolliePlugin\Refund\Checker\MollieOrderRefundCheckerInterface;
use Sylius\MolliePlugin\Refund\OrderRefundInterface;
use Sylius\MolliePlugin\Refund\PaymentRefundInterface;
use Sylius\MolliePlugin\Voucher\Updater\OrderVoucherAdjustmentUpdaterInterface;
use Webmozart\Assert\Assert;

final class StatusAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private PaymentRefundInterface $paymentRefund,
        private OrderRefundInterface $orderRefund,
        private MollieLoggerActionInterface $loggerAction,
        private OrderVoucherAdjustmentUpdaterInterface $orderVoucherAdjustmentUpdater,
        private MollieOrderRefundCheckerInterface $mollieOrderRefundChecker,
    ) {
    }

    /** @param GetStatusInterface|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $details = $payment->getDetails();

        if (
            !isset($details['payment_mollie_id']) &&
            !isset($details['subscription_mollie_id']) &&
            !isset($details['order_mollie_id']) &&
            !isset($details['statusError'])
        ) {
            $request->markNew();
            $this->loggerAction->addLog(sprintf('Mark new payment with id %s', $payment->getId()));

            return;
        }

        if (isset($details['statusError'])) {
            $request->markFailed();

            return;
        }

        if (true === isset($details['subscription_mollie_id'])) {
            try {
                $customer = $this->mollieApiClient->customers->get($details['customer_mollie_id']);
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(sprintf('Error with get customer from mollie with: %s', $e->getMessage()));

                throw new ApiException(sprintf('Error with get customer from mollie with: %s', $e->getMessage()));
            }

            $subscription = $customer->getSubscription($details['subscription_mollie_id']);

            match ($subscription->status) {
                SubscriptionStatus::STATUS_CANCELED => $request->markCanceled(),
                SubscriptionStatus::STATUS_ACTIVE, SubscriptionStatus::STATUS_PENDING, SubscriptionStatus::STATUS_COMPLETED, SubscriptionStatus::STATUS_SUSPENDED => $request->markCaptured(),
                default => $request->markUnknown(),
            };

            $this->loggerAction->addLog(sprintf('Mark subscription status to: %s', $subscription->status));

            return;
        }

        $molliePayment = null;
        if (isset($details['payment_mollie_id'])) {
            try {
                $molliePayment = $this->mollieApiClient->payments->get($details['payment_mollie_id']);
            } catch (\Exception) {
                $this->loggerAction->addNegativeLog(sprintf('Error with get payment in status action with id %s', $details['payment_mollie_id']));

                throw new ApiException(sprintf('Error with get payment in status action with id %s', $details['payment_mollie_id']));
            }
        }

        $order = null;
        if (isset($details['order_mollie_id'])) {
            try {
                $order = $this->mollieApiClient->orders->get($details['order_mollie_id'], ['embed' => 'payments']);
                $payments = $order->_embedded->payments;

                /** @var Payment $payment */
                $payment = current($payments);

                /** @var Payment $molliePayment */
                $molliePayment = $this->mollieApiClient->payments->get($payment->id);
                $molliePayment->metadata = $order->metadata;

                if (MealVoucher::MEAL_VOUCHERS === $payment->method) {
                    $this->orderVoucherAdjustmentUpdater->update($molliePayment, $order->metadata->order_id);
                }
            } catch (\Exception) {
                $this->loggerAction->addNegativeLog(sprintf('Error with get payment page with id %s', $details['payment_mollie_id']));

                throw new ApiException(sprintf('Error with get payment page with id %s', $details['payment_mollie_id']));
            }
        }

        Assert::notNull($molliePayment);
        if ($molliePayment->hasRefunds() || $molliePayment->hasChargebacks()) {
            if (isset($details['order_mollie_id'])) {
                Assert::notNull($order);

                $mollieOrderLinesRefundable = $this->mollieOrderRefundChecker->check($order);

                if ($mollieOrderLinesRefundable) {
                    $this->orderRefund->refund($order);
                    $this->loggerAction->addLog(sprintf('Mark payment order refunded to: %s', $molliePayment->status));
                } else {
                    $this->paymentRefund->refund($molliePayment);
                    $this->loggerAction->addLog(sprintf('Mark payment refunded to: %s', $molliePayment->status));
                }

                return;
            }

            $this->paymentRefund->refund($molliePayment);

            $this->loggerAction->addLog(sprintf('Mark payment refunded to: %s', $molliePayment->status));

            return;
        }

        match ($molliePayment->status) {
            PaymentStatus::STATUS_PENDING, PaymentStatus::STATUS_OPEN => $request->markPending(),
            PaymentStatus::STATUS_AUTHORIZED => $request->markAuthorized(),
            PaymentStatus::STATUS_PAID => $request->markCaptured(),
            PaymentStatus::STATUS_CANCELED => $request->markCanceled(),
            PaymentStatus::STATUS_FAILED => $request->markFailed(),
            PaymentStatus::STATUS_EXPIRED => $request->markExpired(),
            default => $request->markUnknown(),
        };

        $this->loggerAction->addLog(sprintf('Mark payment status to: %s', $molliePayment->status));
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface;
    }
}
