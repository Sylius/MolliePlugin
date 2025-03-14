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

namespace Sylius\MolliePlugin\EventListener;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CheckoutOrderCollidingProductsListener
{
    private RouterInterface $router;

    private TranslatorInterface $translator;

    private RequestStack $requestStack;

    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator,
        RequestStack $requestStack
    ) {
        $this->router = $router;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    public function onUpdate(ResourceControllerEvent $event): void
    {
        if (false === ($subject = $event->getSubject()) instanceof OrderInterface) {
            return;
        }

        if (true === $subject->hasNonRecurringContents() && true === $subject->hasRecurringContents()) {
            $url = $this->router->generate('sylius_shop_cart_summary');
            $response = new RedirectResponse($url);
            $event->setResponse($response);
            $message = $this->translator->trans('sylius_mollie_plugin.order_checkout.colliding_products');
            $event->stop(
                $message,
                ResourceControllerEvent::TYPE_WARNING
            );
            /** @var Session $session */
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('error', $message);
        }
    }
}
