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

namespace Sylius\MolliePlugin\Updater;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\MollieMethodsException;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;

final class MolliePaymentMethodsSynchronizer implements MolliePaymentMethodsSynchronizerInterface
{
    public function __construct(
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly ChannelContextInterface $channelContext,
        private readonly MollieMethodsUpdaterInterface $mollieMethodsUpdater,
    ) {
    }

    public function sync(): void
    {
        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return;
        }

        /** @var PaymentMethodInterface[] $paymentMethods */
        $paymentMethods = $this->paymentMethodRepository->findBy(['enabled' => true]);
        foreach ($paymentMethods as $paymentMethod) {
            if (!$paymentMethod->getChannels()->contains($channel)) {
                continue;
            }

            $gatewayConfig = $this->getMollieBasedGatewayConfig($paymentMethod);
            if (null === $gatewayConfig) {
                continue;
            }

            try {
                $this->mollieMethodsUpdater->update($gatewayConfig);
            } catch (MollieMethodsException) {
            }
        }
    }

    private function getMollieBasedGatewayConfig(PaymentMethodInterface $paymentMethod): ?GatewayConfigInterface
    {
        /** @var GatewayConfigInterface|null $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (null === $gatewayConfig) {
            return null;
        }
        if (!in_array($gatewayConfig->getFactoryName(), [
            MollieGatewayFactory::FACTORY_NAME,
            MollieSubscriptionGatewayFactory::FACTORY_NAME,
        ], true)) {
            return null;
        }

        return $gatewayConfig;
    }
}
