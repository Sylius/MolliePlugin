<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider\Order;

use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Payum\Core\Payum;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\AdminOrderCreationPlugin\Provider\PaymentTokenProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Payment\Exception\NotProvidedOrderPaymentException;
use Sylius\Component\Payment\Exception\UnresolvedDefaultPaymentMethodException;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\StateMachine\StateMachineInterface;
use Webmozart\Assert\Assert;

final class OrderPaymentApplePayDirectProvider implements OrderPaymentApplePayDirectProviderInterface
{
    /** @var PaymentFactoryInterface */
    private $paymentFactory;

    /** @var StateMachineFactoryInterface */
    private $stateMachineFactory;

    /** @var RepositoryInterface */
    private $paymentMethodRepository;

    /** @var RepositoryInterface */
    private $gatewayConfigRepository;

    /** @var PaymentTokenProviderInterface */
    private $paymentTokenProvider;

    /** @var Payum */
    private $payum;

    public function __construct(
        PaymentFactoryInterface $paymentFactory,
        StateMachineFactoryInterface $stateMachineFactory,
        RepositoryInterface $paymentMethodRepository,
        RepositoryInterface $gatewayConfigRepository,
        PaymentTokenProviderInterface $paymentTokenProvider,
        Payum $payum
    ) {
        $this->paymentFactory = $paymentFactory;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->gatewayConfigRepository = $gatewayConfigRepository;
        $this->paymentTokenProvider = $paymentTokenProvider;
        $this->payum = $payum;
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

        /** @var StateMachineInterface $stateMachine */
        $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        $targetTransition = $stateMachine->getTransitionToState($targetState);

        if (null !== $targetTransition) {
            $stateMachine->apply($targetTransition);
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
        } catch (UnresolvedDefaultPaymentMethodException $exception) {
            return null;
        }
    }
}
