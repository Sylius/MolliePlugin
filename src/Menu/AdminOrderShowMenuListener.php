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

final class AdminOrderShowMenuListener
{
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
