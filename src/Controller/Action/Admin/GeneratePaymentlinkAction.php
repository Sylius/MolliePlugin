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

namespace SyliusMolliePlugin\Controller\Action\Admin;

use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\Entity\TemplateMollieEmailInterface;
use SyliusMolliePlugin\Form\Type\PaymentlinkType;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Resolver\PaymentlinkResolverInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class GeneratePaymentlinkAction
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var Environment */
    private $twig;

    /** @var RequestStack */
    private $requestStack;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var PaymentlinkResolverInterface */
    private $paymentlinkResolver;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Environment $twig,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        FormFactoryInterface $formFactory,
        MollieApiClient $mollieApiClient,
        PaymentlinkResolverInterface $paymentlinkResolver,
        MollieLoggerActionInterface $loggerAction
    ) {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->orderRepository = $orderRepository;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->mollieApiClient = $mollieApiClient;
        $this->paymentlinkResolver = $paymentlinkResolver;
        $this->loggerAction = $loggerAction;
    }

    public function __invoke(Request $request): Response
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneByNumber($request->attributes->get('orderNumber'));

        $form = $this->formFactory->create(PaymentlinkType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $paymentlink = $this->paymentlinkResolver->resolve($order, $form->getData(), TemplateMollieEmailInterface::PAYMENT_LINK);

                $this->requestStack->getSession()->getFlashBag()->add('success', $paymentlink);

                $this->loggerAction->addLog(sprintf('Created payment link to order with id = %s', $order->getId()));

                return new RedirectResponse($this->router->generate('sylius_admin_order_show', ['id' => $order->getId()]));
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(sprintf('Error with generate payment link with : %s', $e->getMessage()));

                $this->requestStack->getSession()->getFlashBag()->add('error', $e->getMessage());
            }
        }

        return new Response(
            $this->twig->render('@SyliusMolliePlugin/Admin/Paymentlink/_form.html.twig', [
                'order' => $order,
                'form' => $form->createView(),
            ])
        );
    }
}
