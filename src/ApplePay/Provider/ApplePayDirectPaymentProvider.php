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

namespace Sylius\MolliePlugin\ApplePay\Provider;

use Doctrine\Common\Collections\Collection;
use Mollie\Api\Types\PaymentMethod;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\ApplePay\Resolver\ApplePayDirectPaymentTypeResolverInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Webmozart\Assert\Assert;

final class ApplePayDirectPaymentProvider implements ApplePayDirectPaymentProviderInterface
{
    public function __construct(
        private readonly ApplePayDirectPaymentTypeResolverInterface $applePayDirectPaymentTypeResolver,
    ) {
    }

    public function provideApplePayPayment(OrderInterface $order, array $applePayPaymentToken): void
    {
        /** @var PaymentInterface $payment */
        $payment = $order->getLastPayment();
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        /** @var Collection $mollieGatewayConfig */
        $mollieGatewayConfig = $gatewayConfig->getMollieGatewayConfig();
        if ($mollieGatewayConfig->isEmpty()) {
            return;
        }

        $applePayConfig = $gatewayConfig->getMethodByName(PaymentMethod::APPLEPAY);
        Assert::notNull($applePayConfig);

        if (!$applePayConfig->isEnabled()) {
            return;
        }

        $this->applePayDirectPaymentTypeResolver->resolve($applePayConfig, $payment, $applePayPaymentToken);
    }
}
