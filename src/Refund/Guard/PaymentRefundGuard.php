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

namespace Sylius\MolliePlugin\Refund\Guard;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;

final class PaymentRefundGuard implements PaymentRefundGuardInterface
{
    private const REFUND_PLUGIN_NAME = 'SyliusRefundPlugin';

    private bool $refundLoaded = false;

    /** @param array<string, mixed> $loadedBundles */
    public function __construct(array $loadedBundles)
    {
        $this->initialize($loadedBundles);
    }

    public function isRefundPossible(PaymentInterface $payment): bool
    {
        $gatewayConfig = $payment->getMethod()?->getGatewayConfig();
        if (null === $gatewayConfig) {
            return false;
        }

        $factoryName = $gatewayConfig->getFactoryName();
        if ($factoryName === MollieSubscriptionGatewayFactory::FACTORY_NAME) {
            return false;
        }

        if ($factoryName !== MollieGatewayFactory::FACTORY_NAME) {
            return true;
        }

        return $this->refundLoaded;
    }

    /** @param array<string, string> $bundles */
    private function initialize(array $bundles): void
    {
        foreach ($bundles as $name => $bundle) {
            if ($name === self::REFUND_PLUGIN_NAME || str_contains($bundle, self::REFUND_PLUGIN_NAME)) {
                $this->refundLoaded = true;

                return;
            }
        }
    }
}
