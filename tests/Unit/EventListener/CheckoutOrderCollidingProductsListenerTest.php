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

namespace Tests\Sylius\MolliePlugin\Unit\EventListener;

use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\EventListener\CheckoutOrderCollidingProductsListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CheckoutOrderCollidingProductsListenerTest extends TestCase
{
    private RouterInterface $routerMock;

    private TranslatorInterface $translatorMock;

    private FlashBagInterface $flashBagMock;

    private CheckoutOrderCollidingProductsListener $checkoutOrderCollidingProductsListener;

    protected function setUp(): void
    {
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->translatorMock = $this->createMock(TranslatorInterface::class);

        $sessionMock = $this->createMock(Session::class);
        $this->flashBagMock = $this->createMock(FlashBagInterface::class);

        $sessionMock->method('getFlashBag')->willReturn($this->flashBagMock);

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getSession')->willReturn($sessionMock);

        $this->checkoutOrderCollidingProductsListener = new CheckoutOrderCollidingProductsListener(
            $this->routerMock,
            $this->translatorMock,
            $requestStackMock,
        );
    }

    public function testExecuteOnUpdate(): void
    {
        $eventMock = $this->createMock(ResourceControllerEvent::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $message = 'There are mixed products (recurring and non-recurring) within your cart.
             Proceeding with mixed content is forbidden.'
        ;

        $orderMock->expects($this->once())->method('hasNonRecurringContents')->willReturn(true);
        $orderMock->expects($this->once())->method('hasRecurringContents')->willReturn(true);
        $this->routerMock->expects($this->once())->method('generate')->with('sylius_shop_cart_summary')->willReturn('/sylius_shop_cart_summary');
        $eventMock->expects($this->once())->method('getSubject')->willReturn($orderMock);
        $eventMock->setResponse(new RedirectResponse('/sylius_shop_cart_summary'));

        $this->translatorMock->expects($this->once())->method('trans')->with('sylius_mollie.order_checkout.colliding_products')
            ->willReturn($message)
        ;
        $this->flashBagMock->expects($this->once())->method('add')->with('error', $message);
        $eventMock->expects($this->once())->method('stop')->with($message, ResourceControllerEvent::TYPE_WARNING);
        $this->checkoutOrderCollidingProductsListener->onUpdate($eventMock);
    }
}
