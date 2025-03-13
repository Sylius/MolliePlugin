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

namespace SyliusMolliePlugin\Action\Api;

use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Parser\Response\GuzzleNegativeResponseParserInterface;
use SyliusMolliePlugin\Request\Api\CreateOnDemandSubscriptionPayment;
use SyliusMolliePlugin\Request\Api\CreateSepaMandate;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;

final class CreateOnDemandPaymentAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    private MollieLoggerActionInterface $loggerAction;

    private GuzzleNegativeResponseParserInterface $guzzleNegativeResponseParser;

    public function __construct(
        MollieLoggerActionInterface $loggerAction,
        GuzzleNegativeResponseParserInterface $guzzleNegativeResponseParser
    ) {
        $this->loggerAction = $loggerAction;
        $this->guzzleNegativeResponseParser = $guzzleNegativeResponseParser;
    }

    /** @param CreateSepaMandate|mixed $request */
    public function execute($request): void
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());

        try {
            $paymentSettings = [
                'method' => $details['metadata']['molliePaymentMethods'],
                'cardToken' => $details['metadata']['cartToken'],
                'amount' => $details['amount'],
                'customerId' => $details['customerId'] ?? null,
                'description' => $details['description'],
                'webhookUrl' => $details['webhookUrl'],
                'metadata' => $details['metadata'],
                'mandateId' => $details['mandateId'],
                'sequenceType' => 'recurring',
            ];
            /** @var Payment $payment */
            $payment = $this->mollieApiClient->payments->create($paymentSettings);
        } catch (ApiException $e) {
            $message = $this->guzzleNegativeResponseParser->parse($e);
            $this->loggerAction->addNegativeLog(sprintf('Error with create payment with: %s', $e->getMessage()));

            if ('' === $message) {
                throw new ApiException(sprintf('Error with create payment with: %s', $e->getMessage()));
            }

            $details['statusError'] = $message;

            return;
        } catch (\Exception $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error with create payment with: %s', $e->getMessage()));

            throw new ApiException(sprintf('Error with create payment with: %s', $e->getMessage()));
        }

        $details['payment_mollie_id'] = $payment->id;

        $this->loggerAction->addLog(sprintf('Create payment in mollie with id: %s', $payment->id));
    }

    public function supports($request): bool
    {
        if (
            false === $request instanceof CreateOnDemandSubscriptionPayment
            || false === $request->getModel() instanceof \ArrayAccess) {
            return false;
        }
        $details = ArrayObject::ensureArrayObject($request->getModel());

        return 'recurring' === ($details['metadata']['sequenceType'] ?? 'first');
    }
}
