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
use Mollie\Api\Exceptions\ApiException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Webmozart\Assert\Assert;

final class RefundAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    /** @var ConvertRefundDataInterface */
    private $convertOrderRefundData;

    public function __construct(
        MollieLoggerActionInterface $loggerAction,
        ConvertRefundDataInterface $convertOrderRefundData
    ) {
        $this->loggerAction = $loggerAction;
        $this->convertOrderRefundData = $convertOrderRefundData;
    }

    /** @param Refund|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!array_key_exists('refund', $details['metadata'])) {
            return;
        }

        try {
            $molliePayment = $this->mollieApiClient->payments->get($details['payment_mollie_id']);
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));

            throw new \Exception(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
        }

        if (!$this->shouldBeRefunded($details)) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        try {
            $molliePayment = $this->mollieApiClient->payments->get($details['payment_mollie_id']);

            Assert::notNull($payment->getCurrencyCode());
            $refundData = $this->convertOrderRefundData->convert($details['metadata']['refund'], $payment->getCurrencyCode());

            if (true === $molliePayment->canBeRefunded()) {
                $molliePayment->refund(['amount' => $refundData]);
                $this->loggerAction->addLog(sprintf('Refund action with payment id %s', $molliePayment->id));
            } else {
                $this->loggerAction->addNegativeLog(sprintf('Payment %s can not be refunded.', $molliePayment->id));

                throw new UpdateHandlingException(sprintf('Payment %s can not be refunded.', $molliePayment->id));
            }
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));

            throw new \Exception(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
