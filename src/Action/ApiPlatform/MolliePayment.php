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

namespace Sylius\MolliePlugin\Action\ApiPlatform;

use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sylius\Component\Core\Model\OrderInterface;

class MolliePayment
{
    private const MOLLIE_PAYMENT_METHOD_NAME = 'mollie';

    /**
     * MolliePayment constructor
     */
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @param PaymentMethodInterface $paymentMethod
     *
     * @return bool
     */
    public function supports(PaymentMethodInterface $paymentMethod): bool
    {
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        return $gatewayConfig->getFactoryName() === self::MOLLIE_PAYMENT_METHOD_NAME;
    }

    /**
     * @param PaymentInterface $payment
     *
     * @return array
     */
    public function provideConfiguration(PaymentInterface $payment): array
    {
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $details = $payment->getDetails();
        $methodName = $details['molliePaymentMethods'] ?? null;

        if (!$methodName) {
            if (isset($details['metadata']['molliePaymentMethods'])) {
                $methodName = $details['metadata']['molliePaymentMethods'];
            }
        }

        $redirectUrl = $this->urlGenerator->generate('sylius_mollie_plugin_payum', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $webhookUrl = $this->urlGenerator->generate('sylius_mollie_plugin_payment_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $redirectUrl .= '?orderId=' . $order->getId();
        $webhookUrl .= '?orderId=' . $order->getId();

        return [
            "method" => $methodName,
            "issuer" => $details['selected_issuer'] ?? null,
            "cardToken" => $details['metadata']['cartToken'] ?? null,
            "amount" => $details['amount'] ?? null,
            "customerId" => $details['customerId'] ?? null,
            "description" => $details['description'] ?? null,
            "redirectUrl" => $redirectUrl,
            "webhookUrl" => $webhookUrl,
            "metadata" => $details['metadata'] ?? null,
            "locale" => $details['locale'] ?? null
        ];
    }
}
