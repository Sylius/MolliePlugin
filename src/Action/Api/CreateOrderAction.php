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

use Mollie\Api\Exceptions\ApiException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Request\Api\CreateOrder;
use Sylius\MolliePlugin\Resolver\PaymentMethodConfigResolverInterface;
use Webmozart\Assert\Assert;

final class CreateOrderAction extends BaseApiAwareAction implements ActionInterface
{
    use GatewayAwareTrait;

    public function __construct(private PaymentMethodConfigResolverInterface $methodConfigResolver, private MollieLoggerActionInterface $loggerAction)
    {
    }

    public function execute($request): void
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $customerId = $details['metadata']['customer_mollie_id'] ?? null;
        $method = $details['metadata']['molliePaymentMethods'] ?? '';

        if (null !== $method) {
            /** @var MollieGatewayConfigInterface $paymentMethod */
            $paymentMethod = $this->methodConfigResolver->getConfigFromMethodId($method);

            $orderExpiredTime = $paymentMethod->getOrderExpiration();
            $interval = new \DateInterval('P' . $orderExpiredTime . 'D');
            $dateExpired = new \DateTimeImmutable('now');
            $dateExpired = $dateExpired->add($interval);
        }

        try {
            $order = $this->mollieApiClient->orders->create([
                'method' => $method,
                'payment' => [
                    'cardToken' => $details['metadata']['cartToken'],
                    'customerId' => $customerId,
                    'webhookUrl' => $details['webhookUrl'],
                ],
                'amount' => $details['amount'],
                'billingAddress' => $details['billingAddress'],
                'shippingAddress' => $details['shippingAddress'],
                'metadata' => $details['metadata'],
                'locale' => $details['locale'],
                'orderNumber' => $details['orderNumber'],
                'redirectUrl' => $details['backurl'],
                'webhookUrl' => $details['webhookUrl'],
                'lines' => $details['lines'],
                'expiresAt' => isset($dateExpired) ?
                    $dateExpired->format('Y-m-d') :
                    (new \DateTimeImmutable('now'))->format('Y-m-d'),
            ]);
        } catch (\Exception $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error with create order with: %s', $e->getMessage()));

            throw new ApiException(sprintf('Error with create order with: %s', $e->getMessage()));
        }

        $details['order_mollie_id'] = $order->id;

        $this->loggerAction->addLog(sprintf('Create new order in mollie with id: %s', $order->id));

        Assert::notNull($order->getCheckoutUrl());

        throw new HttpRedirect($order->getCheckoutUrl());
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreateOrder &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
