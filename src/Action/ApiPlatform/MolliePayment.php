<?php

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

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * MolliePayment constructor
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
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
            "issuer" => isset($details['selected_issuer'])  ? $details['selected_issuer'] : null,
            "cardToken" => isset($details['metadata']['cartToken'])  ? $details['metadata']['cartToken'] : null,
            "amount" => isset($details['amount'])  ? $details['amount'] : null,
            "customerId" => isset($details['customerId'])  ? $details['customerId'] : null,
            "description" => isset($details['description'])  ? $details['description'] : null,
            "redirectUrl" => $redirectUrl,
            "webhookUrl" => $webhookUrl,
            "metadata" => isset($details['metadata'])  ? $details['metadata'] : null,
            "locale" => isset($details['locale'])  ? $details['locale'] : null
        ];
    }
}
