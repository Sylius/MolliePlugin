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

namespace Sylius\MolliePlugin\Resolver;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactoryInterface;

final class PaymentLocaleResolver implements PaymentLocaleResolverInterface
{
    public function resolveFromOrder(OrderInterface $order): ?string
    {
        /** @var string $orderLocale */
        $orderLocale = $order->getLocaleCode();

        if (false === array_key_exists($orderLocale, MollieGatewayFactoryInterface::LOCALES_AVAILABLE_MAP)) {
            return null;
        }

        return MollieGatewayFactoryInterface::LOCALES_AVAILABLE_MAP[$orderLocale];
    }
}
