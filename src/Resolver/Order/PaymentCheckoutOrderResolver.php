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

namespace Sylius\MolliePlugin\Resolver\Order;

use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final class PaymentCheckoutOrderResolver implements PaymentCheckoutOrderResolverInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CartContextInterface $cartContext,
        private readonly RepositoryInterface $orderRepository,
    ) {
    }

    public function resolve(): OrderInterface
    {
        $order = null;
        Assert::notNull($this->requestStack->getCurrentRequest());
        $tokenValue = $this->requestStack->getCurrentRequest()->get('tokenValue');

        if (null !== $tokenValue) {
            $order = $this->orderRepository->findOneBy(['tokenValue' => $tokenValue]);
        }

        if (!$order instanceof OrderInterface) {
            $order = $this->cartContext->getCart();
        }

        if ($order instanceof OrderInterface) {
            return $order;
        }

        throw new NotFoundHttpException('Order was not found');
    }
}
