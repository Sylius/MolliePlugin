<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\EventListener;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\EventListener\CheckoutOrderCollidingProductsListener;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CheckoutOrderCollidingProductsListenerSpec extends ObjectBehavior
{
    function let(
        RouterInterface $router,
        TranslatorInterface $translator,
        FlashBagInterface $flashBag
    ): void {
        $this->beConstructedWith(
            $router,
            $translator,
            $flashBag
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(CheckoutOrderCollidingProductsListener::class);
    }

    function it_execute_on_update(
        ResourceControllerEvent $event,
        OrderInterface $order,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ): void {
        $order->hasNonRecurringContents()->willReturn(true);
        $order->hasRecurringContents()->willReturn(true);
        $router->generate('sylius_shop_cart_summary')->willReturn('/sylius_shop_cart_summary');
        $event->getSubject()->willReturn($order);
        $event->setResponse(new RedirectResponse('/sylius_shop_cart_summary'));

        $message = 'There are mixed products (recurring and non-recurring) within your cart.
             Proceeding with mixed content is forbidden.';

        $translator->trans('sylius_mollie_plugin.order_checkout.colliding_products')
            ->willReturn($message);

        $flashBag->add('error', $message)->shouldBeCalledOnce();
        $event->stop($message,ResourceControllerEvent::TYPE_WARNING)->shouldBeCalledOnce();

        $this->onUpdate($event);
    }
}
