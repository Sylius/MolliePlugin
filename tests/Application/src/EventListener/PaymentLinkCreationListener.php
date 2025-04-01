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

namespace Tests\Sylius\MolliePlugin\Application\src\EventListener;

use Doctrine\Persistence\ObjectManager;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Sylius\AdminOrderCreationPlugin\Provider\PaymentTokenProviderInterface;
use Sylius\AdminOrderCreationPlugin\Sender\OrderPaymentLinkSenderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final class PaymentLinkCreationListener
{
    public function __construct(
        private readonly PaymentTokenProviderInterface $paymentTokenProvider,
        private readonly ObjectManager $orderManager,
        private readonly OrderPaymentLinkSenderInterface $orderPaymentLinkSender,
        private readonly Payum $payum,
    ) {
    }

    public function setPaymentLink(GenericEvent $event): void
    {
        /** @var OrderInterface $order */
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $payment = $order->getLastPayment(PaymentInterface::STATE_NEW);
        if (null === $payment) {
            return;
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if ('offline' === $gatewayConfig->getGatewayName()) {
            return;
        }

        $token = $this->paymentTokenProvider->getPaymentToken($payment);

        if (true === in_array($paymentMethod->getGatewayConfig()->getFactoryName(), [MollieGatewayFactory::FACTORY_NAME, MollieSubscriptionGatewayFactory::FACTORY_NAME], true)) {
            $notifyToken = $this->payum->getTokenFactory()->createNotifyToken($token->getGatewayName(), $token->getDetails());
            $refundToken = $this->payum->getTokenFactory()->createRefundToken($token->getGatewayName(), $token->getDetails());

            $payment->setDetails([
                'payment_id' => $token->getDetails()->getId(),
                'backurl' => $token->getAfterUrl(),
                'webhookUrl' => $notifyToken->getTargetUrl(),
                'refund_token' => $refundToken->getHash(),
            ]);
        } else {
            $payment->setDetails(['payment-link' => $token->getAfterUrl()]);
        }

        $this->orderPaymentLinkSender->sendPaymentLink($order);
        $this->orderManager->flush();
    }
}
