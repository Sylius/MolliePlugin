<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SyliusMolliePlugin\Controller\Action\Shop;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;

class PageRedirectController
{
    private const ORDER_COMPLETED_STATE = 'completed';

    public function __construct(
        private RouterInterface $router,
        private OrderRepositoryInterface $orderRepository
    )
    {
    }

    public function thankYouAction(Request $request, SessionInterface $session): RedirectResponse
    {
        $orderId = $request->get('orderId');
        $session->set('sylius_order_id', $orderId);
        $thankYouPageUrl = $this->router->generate('sylius_shop_order_thank_you');

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        $payment = $order->getLastPayment();
        $orderToken = $order->getTokenValue();

        if ($payment?->getState() === self::ORDER_COMPLETED_STATE) {
            return new RedirectResponse($thankYouPageUrl);
        }
        $cartSummaryUrl = $this->router->generate('sylius_shop_order_show', ['tokenValue' => $orderToken]);

        return new RedirectResponse($cartSummaryUrl);
    }
}
