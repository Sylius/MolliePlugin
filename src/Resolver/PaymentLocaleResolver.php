<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Factory\MollieGatewayFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;

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
