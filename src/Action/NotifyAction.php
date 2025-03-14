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

namespace SyliusMolliePlugin\Action;

use SyliusMolliePlugin\Action\Api\BaseApiAwareAction;
use SyliusMolliePlugin\Action\StateMachine\SetStatusOrderActionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use SyliusMolliePlugin\Request\StateMachine\StatusRecurringSubscription;
use Mollie\Api\Exceptions\ApiException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Symfony\Component\HttpFoundation\Response;

final class NotifyAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var GetHttpRequest */
    private $getHttpRequest;

    /** @var MollieSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var SetStatusOrderActionInterface */
    private $setStatusOrderAction;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    public function __construct(
        GetHttpRequest $getHttpRequest,
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        SetStatusOrderActionInterface $setStatusOrderAction,
        MollieLoggerActionInterface $loggerAction
    ) {
        $this->getHttpRequest = $getHttpRequest;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->setStatusOrderAction = $setStatusOrderAction;
        $this->loggerAction = $loggerAction;
    }

    /** @param Notify|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        $this->gateway->execute($this->getHttpRequest);

        if (true === isset($details['sequenceType'])) {
            try {
                $payment = $this->mollieApiClient->payments->get($this->getHttpRequest->request['id']);
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(
                    sprintf('Error with get customer from mollie with: %s', $e->getMessage())
                );

                throw new ApiException(sprintf('Error with get customer from mollie with: %s', $e->getMessage()));
            }

            if ($details['metadata']['order_id'] === filter_var($payment->metadata->order_id, \FILTER_VALIDATE_INT)) {
                $details['payment_mollie_id'] = $this->getHttpRequest->request['id'];
            }

            $subscriptions = $this->subscriptionRepository->findByOrderId($details['metadata']['order_id']);

            /** @var MollieSubscriptionInterface $subscription */
            foreach ($subscriptions as $subscription) {
                $this->gateway->execute(new StatusRecurringSubscription($subscription, $payment->id));
            }

            $this->loggerAction->addLog(sprintf('Notify payment with id: %s', $payment->id));

            throw new HttpResponse(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        }

        if (true === isset($details['order_mollie_id']) && str_starts_with($this->getHttpRequest->request['id'], 'ord_')) {
            try {
                $order = $this->mollieApiClient->orders->get($this->getHttpRequest->request['id']);
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(
                    sprintf('Error with get order from mollie with: %s', $e->getMessage())
                );

                throw new ApiException('Error to get order with ' . $e->getMessage());
            }

            if ($details['metadata']['order_id'] === filter_var($order->metadata->order_id, \FILTER_VALIDATE_INT)) {
                $details['order_mollie_id'] = $this->getHttpRequest->request['id'];
            }

            $this->setStatusOrderAction->execute($order);

            $this->loggerAction->addLog(sprintf('Notify order with id: %s', $order->id));

            throw new HttpResponse(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
