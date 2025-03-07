<?php


declare(strict_types=1);

namespace SyliusMolliePlugin\Menu;

use SyliusMolliePlugin\Factory\MollieGatewayFactory;
use Sylius\Bundle\AdminBundle\Event\OrderShowMenuBuilderEvent;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class AdminOrderShowMenuListener
{
    public const AVAILABLE_PAYMENT_STATE = [
        PaymentInterface::STATE_NEW,
        PaymentInterface::STATE_PROCESSING,
        PaymentInterface::STATE_CANCELLED,
        PaymentInterface::STATE_FAILED,
    ];

    public function addPaymentlinkButton(OrderShowMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $order = $event->getOrder();

        /** @var ?PaymentInterface $payment */
        $payment = $order->getPayments()->last();

        if (null === $payment) {
            return;
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        if (in_array($payment->getState(), self::AVAILABLE_PAYMENT_STATE, true) &&
            MollieGatewayFactory::FACTORY_NAME === $paymentMethod->getGatewayConfig()->getFactoryName()
        ) {
            $menu
            ->addChild('paymentlink', [
                'route' => 'sylius_mollie_plugin_paymentlink',
                'routeParameters' => ['orderNumber' => $order->getNumber()],
            ])
            ->setAttribute('type', 'transition')
            ->setLabel('sylius_mollie_plugin.ui.paymentlink_generate')
            ->setLabelAttribute('icon', 'link all')
            ->setLabelAttribute('color', 'blue');
        }
    }
}
