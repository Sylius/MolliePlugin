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

namespace Sylius\MolliePlugin\Controller\Shop;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class PageRedirectController
{
    private const ORDER_COMPLETED_STATE = 'completed';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function thankYouAction(Request $request, SessionInterface $session): RedirectResponse
    {
        $orderToken = $request->get('orderToken');
        $thankYouPageUrl = $this->router->generate('sylius_shop_order_thank_you');

        if (null === $orderToken || '' === $orderToken) {
            throw new NotFoundHttpException('Order token is required.');
        }

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneByTokenValue($orderToken);

        if (null === $order) {
            throw new NotFoundHttpException(sprintf('Order with token "%s" does not exist.', $orderToken));
        }

        $session->set('sylius_order_id', $order->getId());
        $payment = $order->getLastPayment();
        $tokenValue = $order->getTokenValue();

        if ($payment?->getState() === self::ORDER_COMPLETED_STATE) {
            return new RedirectResponse($thankYouPageUrl);
        }

        $cartSummaryUrl = $this->router->generate('sylius_shop_order_show', ['tokenValue' => $tokenValue]);

        return new RedirectResponse($cartSummaryUrl);
    }
}
