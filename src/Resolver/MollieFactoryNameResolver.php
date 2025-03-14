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

use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory;

final class MollieFactoryNameResolver implements MollieFactoryNameResolverInterface
{
    public function __construct(private readonly CartContextInterface $cartContext)
    {
    }

    public function resolve(?OrderInterface $order = null): string
    {
        if (null === $order) {
            try {
                $order = $this->cartContext->getCart();
            } catch (\Symfony\Component\HttpFoundation\Exception\SessionNotFoundException) {
                $order = null;
            }
        }

        if (true === $order instanceof OrderInterface && true === $order->hasRecurringContents()) {
            return MollieSubscriptionGatewayFactory::FACTORY_NAME;
        }

        return MollieGatewayFactory::FACTORY_NAME;
    }
}
