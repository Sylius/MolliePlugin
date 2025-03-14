<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\Order;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final class PaymentCheckoutOrderResolver implements PaymentCheckoutOrderResolverInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var CartContextInterface */
    private $cartContext;

    /** @var RepositoryInterface */
    private $orderRepository;

    public function __construct(
        RequestStack $requestStack,
        CartContextInterface $cartContext,
        RepositoryInterface $orderRepository
    ) {
        $this->requestStack = $requestStack;
        $this->cartContext = $cartContext;
        $this->orderRepository = $orderRepository;
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
