<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

final class MollieSubscriptionFactory implements MollieSubscriptionFactoryInterface
{
    private FactoryInterface $decoratedFactory;

    private RouterInterface $router;

    public function __construct(
        FactoryInterface $decoratedFactory,
        RouterInterface $router
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->router = $router;
    }

    public function createNew(): MollieSubscriptionInterface
    {
        /** @var MollieSubscriptionInterface $subscriptionTemplate */
        $subscriptionTemplate = $this->decoratedFactory->createNew();

        return $subscriptionTemplate;
    }

    public function createFromFirstOrder(OrderInterface $order): MollieSubscriptionInterface
    {
        $subscriptionTemplate = $this->createNew();

        Assert::notNull($order->getCustomer());
        $subscriptionTemplate->setCustomer($order->getCustomer());
        $subscriptionTemplate->addOrder($order);

        /** @var PaymentInterface $payment */
        foreach ($order->getPayments() as $payment) {
            $subscriptionTemplate->addPayment($payment);
        }

        return $subscriptionTemplate;
    }

    public function createFromFirstOrderWithOrderItemAndPaymentConfiguration(
        OrderInterface $order,
        OrderItemInterface $orderItem,
        array $paymentConfiguration = [],
        string $mandateId = null
    ): MollieSubscriptionInterface {
        /** @var ProductVariantInterface|null $variant */
        $variant = $orderItem->getVariant();
        if (null === $variant) {
            throw new \InvalidArgumentException(
                sprintf('Variant should be instance of "%s::class".', ProductVariantInterface::class)
            );
        }
        $routerContext = $this->router->getContext();
        $hostname = $routerContext->getHost();

        $subscriptionTemplate = $this->createFromFirstOrder($order);
        $configuration = $subscriptionTemplate->getSubscriptionConfiguration();

        Assert::notNull($variant->getInterval());
        Assert::notNull($variant->getTimes());
        $configuration->setInterval($variant->getInterval());
        $configuration->setNumberOfRepetitions($variant->getTimes());
        $configuration->setPaymentDetailsConfiguration($paymentConfiguration);
        $configuration->setMandateId($mandateId);
        $configuration->setHostName($hostname);
        $subscriptionTemplate->setOrderItem($orderItem);

        return $subscriptionTemplate;
    }
}
