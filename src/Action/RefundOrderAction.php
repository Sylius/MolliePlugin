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

use Sylius\MolliePlugin\Action\Api\BaseApiAwareAction;
use Sylius\MolliePlugin\Helper\ConvertRefundDataInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Request\Api\RefundOrder;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Webmozart\Assert\Assert;

final class RefundOrderAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(private MollieLoggerActionInterface $loggerAction, private ConvertRefundDataInterface $convertOrderRefundData)
    {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!array_key_exists('refund', $details['metadata']) || !$this->shouldBeRefunded($details)) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        Assert::notNull($payment->getCurrencyCode());
        $refundData = $this->convertOrderRefundData->convert($details['metadata']['refund'], $payment->getCurrencyCode());

        $molliePayment = null;

        try {
            $order = $this->mollieApiClient->orders->get($details['order_mollie_id'], ['embed' => 'payments']);
            $embeddedPayments = $order->_embedded->payments;

            /** @var Payment $embeddedPayment */
            foreach ($embeddedPayments as $embeddedPayment) {
                if (PaymentStatus::STATUS_PAID === $embeddedPayment->status) {
                    $molliePayment = $this->mollieApiClient->payments->get($embeddedPayment->id);
                }
            }
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));

            throw new \Exception(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
        }

        Assert::notNull($molliePayment);

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        try {
            $currencyCode = $payment->getCurrencyCode();
            Assert::notNull($currencyCode);

            $refundData = $this->convertOrderRefundData->convert($details['metadata']['refund'], $currencyCode);

            $this->mollieApiClient->payments->refund(
                $molliePayment,
                [
                    'amount' => $refundData,
                ]
            );

            $this->loggerAction->addLog(sprintf('Refund order action with order id: %s', $order->id));
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error refund order action with: %s', $e->getMessage()));

            throw new UpdateHandlingException(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof RefundOrder &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
