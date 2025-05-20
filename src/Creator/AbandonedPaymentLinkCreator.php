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

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface as SyliusOrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface as SyliusGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Repository\Query\AbandonedOrdersQueryInterface;
use Sylius\MolliePlugin\Repository\Query\MollieBasedPaymentMethodQueryInterface;
use Sylius\MolliePlugin\Resolver\PaymentLinkResolverInterface;

final class AbandonedPaymentLinkCreator implements AbandonedPaymentLinkCreatorInterface
{
    public function __construct(
        private readonly PaymentLinkResolverInterface $paymentLinkResolver,
        private readonly AbandonedOrdersQueryInterface $abandonedOrdersQuery,
        private readonly MollieBasedPaymentMethodQueryInterface $mollieBasedPaymentMethodQuery,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function create(): void
    {
        $start = new \DateTime();
        /** @var ChannelInterface[] $channels */
        $channels = $this->channelRepository->findEnabled();
        foreach ($channels as $channel) {
            $gateway = $this->getMollieBasedGatewayInChannel($channel);
            if (null === $gateway) {
                continue;
            }

            foreach ($this->getAbandonedOrders($start, $gateway) as $order) {
                /** @var OrderInterface $order */
                $this->sendAbandonedEmail($order);
            }

            $this->entityManager->flush();
        }
    }

    private function getMollieBasedGatewayInChannel(ChannelInterface $channel): ?SyliusGatewayConfigInterface
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $this->mollieBasedPaymentMethodQuery->getOneByChannelAndFactoryName(
            $channel,
            MollieGatewayFactory::FACTORY_NAME,
        );

        return $paymentMethod?->getGatewayConfig();
    }

    /** @return iterable<SyliusOrderInterface> */
    private function getAbandonedOrders(\DateTime $start, SyliusGatewayConfigInterface $gateway): iterable
    {
        $abandonedEnabled = $gateway->getConfig()['abandoned_email_enabled'] ?? false;
        if (false === $abandonedEnabled) {
            return [];
        }

        $abandonedDuration = $gateway->getConfig()['abandoned_hours'] ?? 4;

        $dateTime = clone $start;
        $dateTime->sub(new \DateInterval(\sprintf('PT%sH', $abandonedDuration)));

        return $this->abandonedOrdersQuery->__invoke($dateTime);
    }

    private function sendAbandonedEmail(OrderInterface $order): void
    {
        /** @var PaymentInterface|false $payment */
        $payment = $order->getPayments()->first();
        if (!$payment instanceof PaymentInterface) {
            return;
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        /** @var SyliusGatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (MollieGatewayFactory::FACTORY_NAME !== $gatewayConfig->getFactoryName()) {
            return;
        }

        $this->paymentLinkResolver->resolve($order, [], TemplateMollieEmailInterface::PAYMENT_LINK_ABANDONED);
        $order->setAbandonedEmail(true);
    }
}
