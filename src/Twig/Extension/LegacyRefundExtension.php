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

namespace Sylius\MolliePlugin\Twig\Extension;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LegacyRefundExtension extends AbstractExtension
{
    public const CONFIG_KEY = 'legacy_refund';

    public function __construct(private MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker)
    {
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('mollie_legacy_refund_enabled', [$this, 'isLegacyRefundEnabled']),
        ];
    }

    public function isLegacyRefundEnabled(PaymentInterface $payment): bool
    {
        /** @var GatewayConfigInterface|null $gatewayConfig */
        $gatewayConfig = $payment->getMethod()?->getGatewayConfig();
        if (null === $gatewayConfig || false === $this->mollieGatewayFactoryChecker->isMollieGateway($gatewayConfig)) {
            return false;
        }

        $config = $gatewayConfig->getConfig();

        return isset($config[self::CONFIG_KEY]) && true === $config[self::CONFIG_KEY];
    }
}
