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

use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Preparer\PaymentLinkEmailPreparerInterface;
use Sylius\MolliePlugin\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Sylius\MolliePlugin\Resolver\PaymentlinkResolverInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class AbandonedPaymentLinkCreator implements AbandonedPaymentLinkCreatorInterface
{
    /** @var PaymentlinkResolverInterface */
    private $paymentLinkResolver;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var PaymentLinkEmailPreparerInterface */
    private $emailPreparer;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var ChannelContextInterface */
    private $channelContext;

    public function __construct(
        PaymentlinkResolverInterface $paymentLinkResolver,
        OrderRepositoryInterface $orderRepository,
        PaymentLinkEmailPreparerInterface $emailPreparer,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ChannelContextInterface $channelContext
    ) {
        $this->paymentLinkResolver = $paymentLinkResolver;
        $this->orderRepository = $orderRepository;
        $this->emailPreparer = $emailPreparer;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->channelContext = $channelContext;
    }

    public function create(): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();
        $paymentMethod = $this->paymentMethodRepository->findOneByChannelAndGatewayFactoryName(
            $channel,
            MollieGatewayFactory::FACTORY_NAME
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
