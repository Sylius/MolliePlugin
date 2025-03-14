<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Processor;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\PaymentDetailsFactoryInterface;
use Sylius\MolliePlugin\Order\SubscriptionOrderClonerInterface;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\Repository\OrderRepositoryInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusCorePayment;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class SubscriptionProcessor implements SubscriptionProcessorInterface
{
    use GatewayAwareTrait;

    private SubscriptionOrderClonerInterface $orderCloner;

    private PaymentFactoryInterface $paymentFactory;

    private OrderRepositoryInterface $orderRepository;

    private PaymentDetailsFactoryInterface $paymentDetailsFactory;

    private MollieSubscriptionRepositoryInterface $subscriptionRepository;

    private Payum $paymentRegistry;

    public function __construct(
        SubscriptionOrderClonerInterface $orderCloner,
        PaymentFactoryInterface $paymentFactory,
        OrderRepositoryInterface $orderRepository,
        PaymentDetailsFactoryInterface $paymentDetailsFactory,
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        Payum $paymentRegistry
    ) {
        $this->orderCloner = $orderCloner;
        $this->paymentFactory = $paymentFactory;
        $this->orderRepository = $orderRepository;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->paymentRegistry = $paymentRegistry;
    }

    public function processNextSubscriptionPayment(MollieSubscriptionInterface $subscription): void
    {
        $this->process($subscription);
    }

    public function processNextPayment(MollieSubscriptionInterface $subscription): void
    {
        $payment = $this->process($subscription);
        $details = $payment->getDetails();
        $gateway = $this->paymentRegistry->getGateway($details['metadata']['gateway']);

        $token = $this->paymentRegistry->getTokenFactory()->createToken(
            $details['metadata']['gateway'],
            $payment,
            'sylius_shop_order_thank_you'
        );
        $gateway->execute(new Capture($token));
    }

    private function process(MollieSubscriptionInterface $subscription): PaymentInterface
    {
        $order = $subscription->getFirstOrder();
        Assert::notNull($order);
        $orderItem = $subscription->getOrderItem();
        $clonedOrder = $this->orderCloner->clone(
            $subscription,
            $order,
            $orderItem
        );
        $payment = $this->providePaymentForClonedOrder(
            $subscription,
            $clonedOrder,
            $orderItem
        );
        $details = $this->paymentDetailsFactory->createForSubscriptionAndOrder(
            $subscription->getSubscriptionConfiguration(),
            $clonedOrder
        );
        $payment->setDetails(
            $details
        );
        $clonedOrder->addPayment($payment);
        $this->orderRepository->add($clonedOrder);

        $subscription->addOrder($clonedOrder);
        $subscription->addPayment($payment);
        $this->subscriptionRepository->add($subscription);

        return $payment;
    }

    private function providePaymentForClonedOrder(
        MollieSubscriptionInterface $subscription,
        OrderInterface $clonedOrder,
        OrderItemInterface $orderItem
    ): SyliusCorePayment {
        Assert::notNull($clonedOrder->getCurrencyCode());
        /** @var SyliusCorePayment $payment */
        $payment = $this->paymentFactory->createWithAmountAndCurrencyCode(
            $clonedOrder->getTotal(),
            $clonedOrder->getCurrencyCode()
        );
        $firstOrder = $subscription->getFirstOrder();
        Assert::notNull($firstOrder);
        $lastPayment = $firstOrder->getLastPayment(PaymentInterface::STATE_COMPLETED);
        Assert::notNull($lastPayment);
        $lastPaymentDetails = $lastPayment->getDetails();

        Assert::keyExists($lastPaymentDetails, 'metadata');
        Assert::keyExists($lastPaymentDetails['metadata'], 'molliePaymentMethods');

        $payment->setMethod($lastPayment->getMethod());
        $payment->setState(PaymentInterface::STATE_NEW);

        return $payment;
    }
}
