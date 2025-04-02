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

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

final class PayumController
{
    private const CHECKOUT_STATE_COMPLETED_STATUS = 'completed';

    private const STATE_MACHINE_COMPLETE_STATE = 'complete';

    public function __construct(
        private readonly Payum $payum,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly RouterInterface $router,
        private readonly FactoryInterface $stateMachineFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws \SM\SMException
     */
    public function __invoke(Request $request): Response
    {
        $orderId = $request->get('orderId');
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);

        if (null === $order) {
            throw new NotFoundHttpException(sprintf('Order with id "%s" does not exist.', $orderId));
        }

        $this->updateOrder($order);
        $request->getSession()->set('sylius_order_id', $order->getId());
        $payment = $order->getLastPayment();
        if (null === $payment) {
            $url = $this->router->generate('sylius_shop_order_thank_you');

            return new RedirectResponse($url);
        }
        $redirectOptions = ['route' => 'sylius_shop_order_after_pay'];
        $token = $this->provideTokenBasedOnPayment($payment, $redirectOptions);

        return new RedirectResponse($token->getTargetUrl());
    }

    private function updateOrder(OrderInterface $resource): void
    {
        if ($resource->getCheckoutState() !== self::CHECKOUT_STATE_COMPLETED_STATUS) {
            $this->entityManager->beginTransaction();
            $this->stateMachineFactory->get($resource, 'sylius_order_checkout')->apply(self::STATE_MACHINE_COMPLETE_STATE);
            $this->entityManager->commit();
        }
    }

    /** @param array<string, mixed> $redirectOptions */
    private function provideTokenBasedOnPayment(PaymentInterface $payment, array $redirectOptions): TokenInterface
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (isset($gatewayConfig->getConfig()['use_authorize']) && true === (bool) $gatewayConfig->getConfig()['use_authorize']) {
            $token = $this->getTokenFactory()->createAuthorizeToken(
                $gatewayConfig->getGatewayName(),
                $payment,
                $redirectOptions['route']
                ?? null,
                $redirectOptions['parameters']
                ?? [],
            );
        } else {
            $token = $this->getTokenFactory()->createCaptureToken(
                $gatewayConfig->getGatewayName(),
                $payment,
                $redirectOptions['route']
                ?? null,
                $redirectOptions['parameters']
                ?? [],
            );
        }

        return $token;
    }

    private function getTokenFactory(): GenericTokenFactoryInterface
    {
        return $this->payum->getTokenFactory();
    }
}
