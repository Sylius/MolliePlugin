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

namespace Sylius\MolliePlugin\ApplePay\Provider;

use Payum\Core\Payum;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Payment\Exception\NotProvidedOrderPaymentException;
use Sylius\Component\Payment\Exception\UnresolvedDefaultPaymentMethodException;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Provider\PaymentTokenProviderInterface;
use Webmozart\Assert\Assert;

final class OrderPaymentApplePayDirectProvider implements OrderPaymentApplePayDirectProviderInterface
{
    public function __construct(
        private readonly PaymentFactoryInterface $paymentFactory,
        private readonly StateMachineInterface $stateMachine,
        private readonly RepositoryInterface $paymentMethodRepository,
        private readonly RepositoryInterface $gatewayConfigRepository,
        private readonly PaymentTokenProviderInterface $paymentTokenProvider,
        private readonly Payum $payum,
    ) {
    }

    public function provideOrderPayment(OrderInterface $order, string $targetState): ?PaymentInterface
    {
        $order->getPayments()->clear();

        Assert::notNull($order->getCurrencyCode());
        /** @var PaymentInterface $payment */
        $payment = $this->paymentFactory->createWithAmountAndCurrencyCode($order->getTotal(), $order->getCurrencyCode());

        $paymentMethod = $this->getDefaultPaymentMethod($payment, $order);
        $lastPayment = $this->getLastPayment($order);

        if (null !== $lastPayment) {
            $paymentMethod = $lastPayment->getMethod();
        }

        if (null === $paymentMethod) {
            throw new NotProvidedOrderPaymentException();
        }

        $payment->setMethod($paymentMethod);
        $this->applyRequiredTransition($payment, $targetState);

        $token = $this->paymentTokenProvider->getPaymentToken($payment);

        $notifyToken = $this->payum->getTokenFactory()->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $refundToken = $this->payum->getTokenFactory()->createRefundToken($token->getGatewayName(), $token->getDetails());

        $payment->setDetails([
            'payment_id' => $token->getDetails()->getId(),
            'backurl' => $token->getAfterUrl(),
            'webhookUrl' => $notifyToken->getTargetUrl(),
            'refund_token' => $refundToken->getHash(),
        ]);

        $order->addPayment($payment);

        return $payment;
    }

    public function applyRequiredTransition(PaymentInterface $payment, string $targetState): void
    {
        if ($targetState === $payment->getState()) {
            return;
        }

        $targetTransition = $this->stateMachine->getTransitionToState($payment, PaymentTransitions::GRAPH, $targetState);

        if (null !== $targetTransition) {
            $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, $targetTransition);
        }
    }

    private function getLastPayment(OrderInterface $order): ?PaymentInterface
    {
        $lastCancelledPayment = $order->getLastPayment(PaymentInterface::STATE_CANCELLED);

        if (null !== $lastCancelledPayment) {
            return $lastCancelledPayment;
        }

        return $order->getLastPayment(PaymentInterface::STATE_FAILED);
    }

    private function getDefaultPaymentMethod(PaymentInterface $payment, OrderInterface $order): ?PaymentMethodInterface
    {
        try {
            $payment->setOrder($order);
            $gateway = $this->gatewayConfigRepository->findOneBy(['factoryName' => MollieGatewayFactory::FACTORY_NAME]);
            Assert::isInstanceOf($gateway, GatewayConfigInterface::class);

            /** @var PaymentMethodInterface $paymentMethod */
            $paymentMethod = $this->paymentMethodRepository->findOneBy([
                'gatewayConfig' => $gateway,
            ]);

            return $paymentMethod;
        } catch (UnresolvedDefaultPaymentMethodException) {
            return null;
        }
    }
}
