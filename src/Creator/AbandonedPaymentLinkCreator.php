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

namespace Sylius\MolliePlugin\Creator;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Sylius\MolliePlugin\Resolver\PaymentLinkResolverInterface;

final class AbandonedPaymentLinkCreator implements AbandonedPaymentLinkCreatorInterface
{
    public function __construct(
        private readonly PaymentLinkResolverInterface $paymentLinkResolver,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly ChannelContextInterface $channelContext,
    ) {
    }

    public function create(): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();
        $paymentMethod = $this->paymentMethodRepository->findOneByChannelAndGatewayFactoryName(
            $channel,
            MollieGatewayFactory::FACTORY_NAME,
        );
        if (null === $paymentMethod) {
            return;
        }

        /** @var ?GatewayConfigInterface $gateway */
        $gateway = $paymentMethod->getGatewayConfig();
        if (null === $gateway) {
            return;
        }

        $abandonedEnabled = $gateway->getConfig()['abandoned_email_enabled'] ?? false;
        if (false === $abandonedEnabled) {
            return;
        }

        $abandonedDuration = $gateway->getConfig()['abandoned_hours'] ?? 4;

        $dateTime = new \DateTime('now');
        $duration = new \DateInterval(\sprintf('PT%sH', $abandonedDuration));
        $dateTime->sub($duration);

        $orders = $this->orderRepository->findAbandonedByDateTime($dateTime);

        /** @var OrderInterface $order */
        foreach ($orders as $order) {
            /** @var PaymentInterface $payment */
            $payment = $order->getPayments()->first();

            /** @var PaymentMethodInterface $paymentMethod */
            $paymentMethod = $payment->getMethod();

            /** @var \Payum\Core\Model\GatewayConfigInterface $gatewayConfig */
            $gatewayConfig = $paymentMethod->getGatewayConfig();

            if (MollieGatewayFactory::FACTORY_NAME === $gatewayConfig->getFactoryName()) {
                $this->paymentLinkResolver->resolve($order, [], TemplateMollieEmailInterface::PAYMENT_LINK_ABANDONED);
                $order->setAbandonedEmail(true);
                $this->orderRepository->add($order);
            }
        }
    }
}
