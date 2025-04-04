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

namespace Sylius\MolliePlugin\Controller\Admin;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailInterface;
use Sylius\MolliePlugin\Form\Type\PaymentlinkType;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Resolver\PaymentLinkResolverInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class GeneratePaymentLinkAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $router,
        private readonly FormFactoryInterface $formFactory,
        private readonly PaymentLinkResolverInterface $paymentLinkResolver,
        private readonly MollieLoggerActionInterface $loggerAction,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneByNumber($request->attributes->get('orderNumber'));

        $form = $this->formFactory->create(PaymentlinkType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Session $session */
            $session = $this->requestStack->getSession();

            try {
                $paymentLink = $this->paymentLinkResolver->resolve($order, $form->getData(), TemplateMollieEmailInterface::PAYMENT_LINK);

                $session->getFlashBag()->add('success', $paymentLink);

                $this->loggerAction->addLog(sprintf('Created payment link to order with id = %s', $order->getId()));

                return new RedirectResponse($this->router->generate('sylius_admin_order_show', ['id' => $order->getId()]));
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(sprintf('Error with generate payment link with : %s', $e->getMessage()));

                $session->getFlashBag()->add('error', $e->getMessage());
            }
        }

        return new Response(
            $this->twig->render('@SyliusMolliePlugin/Admin/Paymentlink/_form.html.twig', [
                'order' => $order,
                'form' => $form->createView(),
            ]),
        );
    }
}
