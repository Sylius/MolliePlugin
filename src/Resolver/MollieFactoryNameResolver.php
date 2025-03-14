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

namespace SyliusMolliePlugin\Resolver;

use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Factory\MollieGatewayFactory;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use Sylius\Component\Order\Context\CartContextInterface;

final class MollieFactoryNameResolver implements MollieFactoryNameResolverInterface
{
    private CartContextInterface $cartContext;

    public function __construct(CartContextInterface $cartContext)
    {
        $this->cartContext = $cartContext;
    }

    public function resolve(OrderInterface $order = null): string
    {
        if (null === $order) {
            try {
                $order = $this->cartContext->getCart();
            } catch (\Symfony\Component\HttpFoundation\Exception\SessionNotFoundException $e) {
                $order = null;
            }
        }

        if (true === $order instanceof OrderInterface && true === $order->hasRecurringContents()) {
            return MollieSubscriptionGatewayFactory::FACTORY_NAME;
        }

        return MollieGatewayFactory::FACTORY_NAME;
    }
}
