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

    private const QR_ORDER_ID_SESSION_KEY = 'sylius_mollie_qr_order_id';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function thankYouAction(Request $request, SessionInterface $session): RedirectResponse
    {
        $orderId = $request->get('orderId');

        if (null === $orderId || '' === $orderId ||
            (string) $session->get(self::QR_ORDER_ID_SESSION_KEY) !== (string) $orderId) {
            throw new NotFoundHttpException('Order not found.');
        }

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);

        if (null === $order) {
            throw new NotFoundHttpException('Order not found.');
        }

        $session->set('sylius_order_id', $order->getId());

        if ($order->getLastPayment()?->getState() === self::ORDER_COMPLETED_STATE) {
            return new RedirectResponse($this->router->generate('sylius_shop_order_thank_you'));
        }

        $tokenValue = $order->getTokenValue();

        if (null === $tokenValue) {
            throw new NotFoundHttpException('Order not found.');
        }

        return new RedirectResponse(
            $this->router->generate('sylius_shop_order_show', ['tokenValue' => $tokenValue]),
        );
    }
}
