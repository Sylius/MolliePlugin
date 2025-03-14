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

namespace Sylius\MolliePlugin\Action\Api;

use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Parser\Response\GuzzleNegativeResponseParserInterface;
use Sylius\MolliePlugin\Request\Api\CreateOnDemandSubscription;
use Sylius\MolliePlugin\Request\Api\CreateSepaMandate;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;

final class CreateOnDemandSubscriptionAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    /** @var GuzzleNegativeResponseParserInterface */
    private $guzzleNegativeResponseParser;

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
                'redirectUrl' => $details['backurl'],
                'webhookUrl' => $details['webhookUrl'],
                'metadata' => $details['metadata'],
                'sequenceType' => 'first',
            ];
            /** @var Payment $payment */
            $payment = $this->mollieApiClient->payments->create($paymentSettings);
        } catch (ApiException $e) {
            $message = $this->guzzleNegativeResponseParser->parse($e);
            $formattedMessage = sprintf('Error with create payment with: %s', $e->getMessage());
            $this->loggerAction->addNegativeLog($formattedMessage);

            if ('' === $message) {
                throw new ApiException($formattedMessage);
            }

            $details['statusError'] = $message;

            return;
        } catch (\Exception $e) {
            $formattedMessage = sprintf('Error with create payment with: %s', $e->getMessage());
            $this->loggerAction->addNegativeLog($formattedMessage);

            throw new ApiException($formattedMessage);
        }

        $details['payment_mollie_id'] = $payment->id;

        $this->loggerAction->addLog(sprintf('Create payment in mollie with id: %s', $payment->id));

        if (null === $payment->getCheckoutUrl()) {
            throw new HttpRedirect($details['backurl']);
        }

        throw new HttpRedirect($payment->getCheckoutUrl());
    }

    public function supports($request): bool
    {
        if (
            false === $request instanceof CreateOnDemandSubscription
            || false === $request->getModel() instanceof \ArrayAccess) {
            return false;
        }
        $details = ArrayObject::ensureArrayObject($request->getModel());

        return 'first' === ($details['metadata']['sequenceType'] ?? 'first');
    }
}
