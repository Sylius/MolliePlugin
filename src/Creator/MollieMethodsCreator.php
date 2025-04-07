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

namespace Sylius\MolliePlugin\Creator;

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Resources\MethodCollection;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Factory\MethodsFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayConfigFactoryInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\MethodInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;

final class MollieMethodsCreator implements MollieMethodsCreatorInterface
{
    public function __construct(
        private readonly MethodsFactoryInterface $methodsFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly MollieGatewayConfigFactoryInterface $factory,
    ) {
    }

    public function createMethods(MethodCollection $allMollieMethods, GatewayConfigInterface $gateway): void
    {
        $methods = $this->methodsFactory->createNew();

        foreach ($allMollieMethods as $mollieMethod) {
            if (in_array($mollieMethod->id, MollieMethodsResolverInterface::UNSUPPORTED_METHODS, true)) {
                continue;
            }

            if (
                MollieSubscriptionGatewayFactory::FACTORY_NAME === $gateway->getFactoryName() &&
                (
                    false === in_array($mollieMethod->id, MollieMethodsResolverInterface::RECURRING_PAYMENT_SUPPORTED_METHODS, true) &&
                    false === in_array($mollieMethod->id, MollieMethodsResolverInterface::RECURRING_PAYMENT_INITIAL_METHODS, true)
                )
            ) {
                continue;
            }

            $methods->add($mollieMethod);
        }

        /** @var MethodInterface $method */
        foreach ($methods->getAllEnabled() as $key => $method) {
            $gatewayConfig = $this->factory->create($method, $gateway, $key);

            $this->entityManager->persist($gatewayConfig);
            $this->entityManager->flush();
        }
    }
}
