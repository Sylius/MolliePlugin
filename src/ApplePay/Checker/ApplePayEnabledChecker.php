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

namespace Sylius\MolliePlugin\ApplePay\Checker;

use Mollie\Api\Types\PaymentMethod;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface as BaseGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

final class ApplePayEnabledChecker implements ApplePayEnabledCheckerInterface
{
    public function __construct(
        private readonly RepositoryInterface $mollieGatewayConfigurationRepository,
        private readonly ?PaymentMethodsResolverInterface $paymentMethodsResolver = null,
    ) {
    }

    public function isEnabled(): bool
    {
        $applePayConfigs = $this->mollieGatewayConfigurationRepository->findBy([
            'methodId' => PaymentMethod::APPLEPAY,
        ]);

        $applePayConfig = $applePayConfigs[0] ?? null;
        if ($applePayConfig instanceof MollieGatewayConfigInterface) {
            return $this->isApplePayDirectEnabled($applePayConfig);
        }

        return false;
    }

    public function isEnabledForOrder(?OrderInterface $order): bool
    {
        $payment = $order?->getLastPayment();
        if (null === $payment || null === $this->paymentMethodsResolver) {
            return $this->isEnabled();
        }

        $supportedMethods = $this->paymentMethodsResolver->getSupportedMethods($payment);
        /** @var PaymentMethodInterface $method */
        foreach ($supportedMethods as $method) {
            $gatewayConfig = $method->getGatewayConfig();
            if (false === $gatewayConfig instanceof BaseGatewayConfigInterface) {
                continue;
            }

            $applePay = $gatewayConfig->getMethodByName(PaymentMethod::APPLEPAY);
            if (null === $applePay) {
                continue;
            }

            return $this->isApplePayDirectEnabled($applePay);
        }

        return false;
    }

    private function isApplePayDirectEnabled(MollieGatewayConfigInterface $mollieGatewayConfig): bool
    {
        return $mollieGatewayConfig->isEnabled() && true === $mollieGatewayConfig->isApplePayDirectButton();
    }
}
