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

namespace SyliusMolliePlugin\Creator;

use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Factory\MethodsFactoryInterface;
use SyliusMolliePlugin\Factory\MollieGatewayConfigFactoryInterface;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use SyliusMolliePlugin\Payments\Methods\MethodInterface;
use SyliusMolliePlugin\Resolver\MollieMethodsResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Resources\MethodCollection;

final class MollieMethodsCreator implements MollieMethodsCreatorInterface
{
    /** @var MethodsFactoryInterface */
    private $methodsFactory;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var MollieGatewayConfigFactoryInterface */
    private $factory;

    public function __construct(
        MethodsFactoryInterface $methodsFactory,
        EntityManagerInterface $entityManager,
        MollieGatewayConfigFactoryInterface $factory
    ) {
        $this->methodsFactory = $methodsFactory;
        $this->entityManager = $entityManager;
        $this->factory = $factory;
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
