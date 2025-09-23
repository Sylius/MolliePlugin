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

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\MollieMethodsException;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Updater\MollieMethodsUpdaterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PaymentMethodsRefreshListener
{
    public function __construct(
        private readonly MollieMethodsUpdaterInterface $mollieMethodsUpdater,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function refreshMethods(ResourceControllerEvent $initializeUpdate): void
    {
        $paymentMethod = $initializeUpdate->getSubject();
        if (!$paymentMethod instanceof PaymentMethodInterface) {
            return;
        }
        if (Request::METHOD_GET !== $this->requestStack->getMainRequest()->getMethod()) {
            return;
        }

        $this->refreshPaymentMethodMollieMethods($paymentMethod);
    }

    private function refreshPaymentMethodMollieMethods(PaymentMethodInterface $paymentMethod): void
    {
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (!$gatewayConfig instanceof GatewayConfigInterface || !in_array($gatewayConfig->getFactoryName(), [
                MollieGatewayFactory::FACTORY_NAME,
                MollieSubscriptionGatewayFactory::FACTORY_NAME,
            ], true)) {
            return;
        }

        $mollieConfig = $gatewayConfig->getMollieGatewayConfig();
        if (null === $mollieConfig || $mollieConfig->isEmpty()) {
            return;
        }

        try {
            $this->mollieMethodsUpdater->update($gatewayConfig);
        } catch (MollieMethodsException) {
        }
    }
}
