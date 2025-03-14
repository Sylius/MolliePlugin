<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory;
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
