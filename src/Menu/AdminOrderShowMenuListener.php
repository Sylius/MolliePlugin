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

namespace Sylius\MolliePlugin\Menu;

use Sylius\Bundle\AdminBundle\Event\OrderShowMenuBuilderEvent;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Webmozart\Assert\Assert;

final class AdminOrderShowMenuListener
{
    public const AVAILABLE_PAYMENT_STATE = [
        PaymentInterface::STATE_NEW,
        PaymentInterface::STATE_PROCESSING,
        PaymentInterface::STATE_CANCELLED,
        PaymentInterface::STATE_FAILED,
    ];

    public function addPaymentLinkButton(OrderShowMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $order = $event->getOrder();

        /** @var PaymentInterface|false|null $payment */
        $payment = $order->getPayments()->last();

        if (!$payment instanceof PaymentInterface) {
            return;
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        if (in_array($payment->getState(), self::AVAILABLE_PAYMENT_STATE, true) &&
            MollieGatewayFactory::FACTORY_NAME === $paymentMethod->getGatewayConfig()->getFactoryName()
        ) {
            $menu
            ->addChild('payment_link', [
                'route' => 'sylius_mollie_plugin_payment_link',
                'routeParameters' => ['orderNumber' => $order->getNumber()],
            ])
            ->setAttribute('type', 'transition')
            ->setLabel('sylius_mollie.ui.payment_link_generate')
            ->setLabelAttribute('icon', 'link all')
            ->setLabelAttribute('color', 'blue');
        }
    }

    public function removeRefundsButton(OrderShowMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $order = $event->getOrder();

        /** @var PaymentInterface|false|null $payment */
        $payment = $order->getPayments()->last();
        if (!$payment instanceof PaymentInterface) {
            return;
        }

        /** @var PaymentMethodInterface|null $method */
        $method = $payment->getMethod();
        $gatewayConfig = $method?->getGatewayConfig();
        if (null === $gatewayConfig) {
            return;
        }

        if (MollieGatewayFactory::FACTORY_NAME === $gatewayConfig->getFactoryName()) {
            $menu->removeChild('refunds');
        }
    }
}
