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

namespace Sylius\MolliePlugin\Payum\Action\Subscription;

use Mollie\Api\Exceptions\ApiException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Sylius\MolliePlugin\Client\Parser\ApiExceptionParserInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction;
use Sylius\MolliePlugin\Payum\Request\CreateSepaMandate;
use Sylius\MolliePlugin\Payum\Request\Subscription\CreateOnDemandSubscription;

final class CreateOnDemandSubscriptionAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private MollieLoggerActionInterface $loggerAction,
        private ApiExceptionParserInterface $apiExceptionParser,
    ) {
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
            /** @throws ApiException|\Exception */
            $payment = $this->mollieApiClient->payments->create($paymentSettings);
        } catch (ApiException $e) {
            $message = $this->apiExceptionParser->parse($e);
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
            false === $request instanceof CreateOnDemandSubscription ||
            false === $request->getModel() instanceof \ArrayAccess) {
            return false;
        }
        $details = ArrayObject::ensureArrayObject($request->getModel());

        return 'first' === ($details['metadata']['sequenceType'] ?? 'first');
    }
}
