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

namespace Sylius\MolliePlugin\Updater;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\MollieMethodsException;
use Sylius\MolliePlugin\Factory\MethodsFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayConfigFactoryInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\MethodInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Provider\Methods\MollieMethodsProviderInterface;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class MollieMethodsUpdater implements MollieMethodsUpdaterInterface
{
    public const DEFAULT_CACHE_TTL = 7200;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly MollieMethodsProviderInterface $methodsProvider,
        private readonly RepositoryInterface $mollieGatewayConfigRepository,
        private readonly MollieGatewayConfigFactoryInterface $mollieGatewayConfigFactory,
        private readonly MethodsFactoryInterface $methodsFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly int $cacheTtl = self::DEFAULT_CACHE_TTL,
    ) {
    }

    public function update(GatewayConfigInterface $gateway, bool $force = false): void
    {
        $cacheKey = $this->generateCacheKey($gateway);

        if (false === $force) {
            $wasRecentlyRefreshed = $this->cache->get($cacheKey, function () {
                return false;
            });

            if ($wasRecentlyRefreshed === true) {
                return;
            }
        }

        try {
            $upstreamMethodsMap = $this->getPurifiedMethodsFromProvider($gateway);
            if ([] === $upstreamMethodsMap) {
                return;
            }
        } catch (MollieMethodsException $exception) {
            throw MollieMethodsException::update($exception);
        }

        /** @var MollieGatewayConfigInterface[] $existingMethods */
        $existingMethods = $this->mollieGatewayConfigRepository->findBy(['gateway' => $gateway]);

        $existingMethodsMap = [];
        $maxPosition = -1;
        foreach ($existingMethods as $existingMethod) {
            $existingMethodsMap[$existingMethod->getMethodId()] = $existingMethod;
            $position = $existingMethod->getPosition();
            if ($position !== null && $position > $maxPosition) {
                $maxPosition = $position;
            }
        }

        foreach ($existingMethodsMap as $methodId => $existingMethod) {
            if (!isset($upstreamMethodsMap[$methodId])) {
                $this->entityManager->remove($existingMethod);
            }
        }

        foreach ($upstreamMethodsMap as $methodId => $providerMethod) {
            if (isset($existingMethodsMap[$methodId])) {
                $mollieGatewayConfig = $existingMethodsMap[$methodId];
                $mollieGatewayConfig->setMinimumAmount($providerMethod->getMinimumAmount());
                $mollieGatewayConfig->setMaximumAmount($providerMethod->getMaximumAmount());

                continue;
            }

            $mollieGatewayConfig = $this->mollieGatewayConfigFactory->create(
                $providerMethod,
                $gateway,
                ++$maxPosition,
            );
            $this->entityManager->persist($mollieGatewayConfig);
        }

        $this->entityManager->flush();

        $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter($this->cacheTtl);

            return true;
        });
    }

    /** @return array<string, MethodInterface> */
    private function getPurifiedMethodsFromProvider(GatewayConfigInterface $gateway): array
    {
        $registry = $this->methodsFactory->createNew();

        $methods = $this->methodsProvider->getMethods($gateway);
        foreach ($methods as $method) {
            if (in_array($method->id, MollieMethodsResolverInterface::UNSUPPORTED_METHODS, true)) {
                continue;
            }
            if (
                MollieSubscriptionGatewayFactory::FACTORY_NAME === $gateway->getFactoryName() &&
                (
                    false === in_array($method->id, MollieMethodsResolverInterface::RECURRING_PAYMENT_SUPPORTED_METHODS, true) &&
                    false === in_array($method->id, MollieMethodsResolverInterface::RECURRING_PAYMENT_INITIAL_METHODS, true)
                )
            ) {
                continue;
            }

            $registry->add($method);
        }

        $purifiedMethods = [];
        foreach ($registry->getAllEnabled() as $method) {
            /** @phpstan-ignore-next-line Not every class which implements MethodInterface returns the same type */
            $purifiedMethods[$method->getMethodId()] = $method;
        }

        return $purifiedMethods;
    }

    private function generateCacheKey(GatewayConfigInterface $gateway): string
    {
        return sprintf('sylius_mollie_methods_%s', $gateway->getId());
    }
}
