<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\PaymentProcessing;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Request\Api\CancelRecurringSubscription;
use Payum\Core\Payum;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class CancelRecurringSubscriptionProcessor implements CancelRecurringSubscriptionProcessorInterface
{
    /** @var Payum */
    private $payum;

    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }

    /**
     * @inheritdoc
     */
    public function process(MollieSubscriptionInterface $subscription): void
    {
        $lastOrder = $subscription->getLastOrder();

        if (null === $lastOrder) {
            return;
        }

        $payment = $lastOrder->getLastPayment();

        if (null === $payment) {
            return;
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        if (null === $paymentMethod->getGatewayConfig() ||
            MollieSubscriptionGatewayFactory::FACTORY_NAME !== $paymentMethod->getGatewayConfig()->getFactoryName()
        ) {
            return;
        }

        $gateway = $this->payum->getGateway($paymentMethod->getGatewayConfig()->getGatewayName());

        $gateway->execute(new CancelRecurringSubscription($subscription));
    }
}
