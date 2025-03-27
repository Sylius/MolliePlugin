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

namespace Sylius\MolliePlugin\Payum\Provider;

use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PaymentTokenProvider implements PaymentTokenProviderInterface
{
    public function __construct(private Payum $payum, private string $afterPayRoute)
    {
    }

    public function getPaymentToken(PaymentInterface $payment): TokenInterface
    {
        $tokenFactory = $this->payum->getTokenFactory();

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (
            isset($gatewayConfig->getConfig()['use_authorize']) &&
            $gatewayConfig->getConfig()['use_authorize'] === true
        ) {
            return $tokenFactory->createAuthorizeToken(
                $gatewayConfig->getGatewayName(),
                $payment,
                $this->afterPayRoute,
            );
        }

        return $tokenFactory->createCaptureToken(
            $gatewayConfig->getGatewayName(),
            $payment,
            $this->afterPayRoute,
        );
    }
}
