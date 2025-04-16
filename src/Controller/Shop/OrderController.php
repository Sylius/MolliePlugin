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

use Sylius\Bundle\OrderBundle\Controller\OrderController as BaseOrderController;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\Component\Resource\ResourceActions;
use Sylius\MolliePlugin\ApplePay\Provider\ApplePayDirectProviderInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class OrderController extends BaseOrderController
{
    public function updateAppleOrderAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
        $resource = $this->findOr404($configuration);

        /** @var ResourceControllerEvent $event */
        $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

        if ($event->isStopped() && !$configuration->isHtmlRequest()) {
            throw new HttpException($event->getErrorCode(), $event->getMessage());
        }

        if ($event->isStopped()) {
            $eventResponse = $event->getResponse();
            if (null !== $eventResponse) {
                return $eventResponse;
            }

            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var OrderInterface $currentCart */
            $currentCart = $this->getCurrentCart();
            $this->getApplePayProviderService()->provideOrder($currentCart, $request);

            /** @var PaymentInterface $payment */
            $payment = $currentCart->getLastPayment();

            if (PaymentInterface::STATE_COMPLETED !== $payment->getState()) {
                $response = [
                    'status' => 1,
                    'errors' => 'Payment not created',
                ];

                return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
            }

            $this->resourceUpdateHandler->handle($resource, $configuration, $this->manager);
        } catch (UpdateHandlingException) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

        $postEventResponse = $postEvent->getResponse();

        if (null !== $postEventResponse) {
            return $postEventResponse;
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::UPDATE, $configuration, $resource);

        $initializeEventResponse = $initializeEvent->getResponse();

        if (null !== $initializeEventResponse) {
            return $initializeEventResponse;
        }

        $dataResponse = [];
        $redirect = $this->redirectToRoute('sylius_shop_order_thank_you');
        $dataResponse['returnUrl'] = $redirect->getTargetUrl();
        $dataResponse['responseToApple'] = ['status' => 0];

        $response = [
            'success' => true,
            'data' => $dataResponse,
        ];

        return new JsonResponse($response, Response::HTTP_OK);
    }

    private function getApplePayProviderService(): ApplePayDirectProviderInterface
    {
        /** @var ApplePayDirectProviderInterface $provider */
        $provider = $this->get('sylius_mollie_plugin.provider.apple.apple_pay_direct_provider');

        return $provider;
    }
}
