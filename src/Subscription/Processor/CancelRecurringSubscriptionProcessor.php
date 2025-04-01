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

namespace Sylius\MolliePlugin\Subscription\Processor;

use Payum\Core\Payum;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Payum\Request\Subscription\CancelRecurringSubscription;

final class CancelRecurringSubscriptionProcessor implements CancelRecurringSubscriptionProcessorInterface
{
    public function __construct(private readonly Payum $payum)
    {
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
