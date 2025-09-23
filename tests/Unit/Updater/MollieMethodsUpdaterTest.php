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

namespace Tests\Sylius\MolliePlugin\Unit\Updater;

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Resources\Method;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Factory\MethodsFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayConfigFactoryInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\MethodInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Provider\Methods\MollieMethodsProviderInterface;
use Sylius\MolliePlugin\Registry\PaymentMethodRegistryInterface;
use Sylius\MolliePlugin\Updater\MollieMethodsUpdater;
use Sylius\MolliePlugin\Updater\MollieMethodsUpdaterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class MollieMethodsUpdaterTest extends TestCase
{
    private CacheInterface $cache;

    private MollieMethodsProviderInterface $methodsProvider;

    private RepositoryInterface $mollieGatewayConfigRepository;

    private MollieGatewayConfigFactoryInterface $mollieGatewayConfigFactory;

    private MethodsFactoryInterface $methodsFactory;

    private EntityManagerInterface $entityManager;

    private MollieMethodsUpdater $updater;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->methodsProvider = $this->createMock(MollieMethodsProviderInterface::class);
        $this->mollieGatewayConfigRepository = $this->createMock(RepositoryInterface::class);
        $this->mollieGatewayConfigFactory = $this->createMock(MollieGatewayConfigFactoryInterface::class);
        $this->methodsFactory = $this->createMock(MethodsFactoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->updater = new MollieMethodsUpdater(
            $this->cache,
            $this->methodsProvider,
            $this->mollieGatewayConfigRepository,
            $this->mollieGatewayConfigFactory,
            $this->methodsFactory,
            $this->entityManager,
            MollieMethodsUpdater::DEFAULT_CACHE_TTL,
        );
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(MollieMethodsUpdaterInterface::class, $this->updater);
    }

    public function testUpdateWithCacheHitReturnsEarlyWhenNotForced(): void
    {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('sylius_mollie_methods_123', $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) {
                return true;
            });

        $this->methodsProvider->expects($this->never())
            ->method('getMethods');

        $this->mollieGatewayConfigRepository->expects($this->never())
            ->method('findBy');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->updater->update($gateway);
    }

    public function testUpdateWithForceBypassesCache(): void
    {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(456);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('sylius_mollie_methods_456', $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) {
                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())
                    ->method('expiresAfter')
                    ->with(7200);

                return $callback($item);
            });

        $this->setupUpstreamMethods($gateway, ['creditcard']);

        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['gateway' => $gateway])
            ->willReturn([]);

        $this->entityManager->expects($this->exactly(1))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->updater->update($gateway, true);
    }

    /** @dataProvider updateReturnsEarlyDataProvider */
    public function testUpdateReturnsEarlyWhenNoUpstreamMethods(bool $force): void
    {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(789);

        if (!$force) {
            $this->cache->expects($this->once())
                ->method('get')
                ->with('sylius_mollie_methods_789', $this->isType('callable'))
                ->willReturnCallback(function ($key, $callback) {
                    return false;
                });
        } else {
            $this->cache->expects($this->never())
                ->method('get');
        }

        $this->setupEmptyUpstreamMethods($gateway);

        $this->mollieGatewayConfigRepository->expects($this->never())
            ->method('findBy');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->updater->update($gateway, $force);
    }

    public static function updateReturnsEarlyDataProvider(): \Generator
    {
        yield 'non-forced update returns early when no upstream methods' => [
            'force' => false,
        ];

        yield 'forced update returns early when no upstream methods' => [
            'force' => true,
        ];
    }

    /** @dataProvider methodRemovalWithUpstreamDataProvider */
    public function testRemovesExistingMethodsNotInUpstream(
        array $upstreamMethods,
        array $existingMethods,
        array $expectedRemovals,
    ): void {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(111);

        $this->cache->expects($this->exactly(2))
            ->method('get')
            ->with('sylius_mollie_methods_111', $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return false;
                }

                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())
                    ->method('expiresAfter')
                    ->with(7200);

                return $callback($item);
            });

        $this->setupUpstreamMethods($gateway, $upstreamMethods);

        $existingMethodMocks = [];
        foreach ($existingMethods as $methodData) {
            $mock = $this->createMock(MollieGatewayConfigInterface::class);
            $mock->expects($this->atLeastOnce())
                ->method('getMethodId')
                ->willReturn($methodData['methodId']);
            $mock->expects($this->atLeastOnce())
                ->method('getPosition')
                ->willReturn($methodData['position']);
            $existingMethodMocks[] = $mock;
        }

        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['gateway' => $gateway])
            ->willReturn($existingMethodMocks);

        if (count($expectedRemovals) > 0) {
            $this->entityManager->expects($this->exactly(count($expectedRemovals)))
                ->method('remove');
        } else {
            $this->entityManager->expects($this->never())
                ->method('remove');
        }

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->updater->update($gateway);
    }

    public static function methodRemovalWithUpstreamDataProvider(): \Generator
    {
        yield 'removes only methods not in upstream' => [
            'upstreamMethods' => ['creditcard'],
            'existingMethods' => [
                ['methodId' => 'creditcard', 'position' => 1],
                ['methodId' => 'ideal', 'position' => 2],
                ['methodId' => 'bancontact', 'position' => 3],
            ],
            'expectedRemovals' => [1, 2],
        ];

        yield 'removes no methods when all exist in upstream' => [
            'upstreamMethods' => ['creditcard', 'ideal'],
            'existingMethods' => [
                ['methodId' => 'creditcard', 'position' => 1],
                ['methodId' => 'ideal', 'position' => 2],
            ],
            'expectedRemovals' => [],
        ];
    }

    /** @dataProvider methodUpdateDataProvider */
    public function testUpdatesExistingMethodsWithNewAmounts(
        array $upstreamMethodsData,
        array $existingMethods,
        array $expectedUpdates,
    ): void {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(222);

        $this->cache->expects($this->exactly(2))
            ->method('get')
            ->with('sylius_mollie_methods_222', $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return false;
                }

                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())
                    ->method('expiresAfter')
                    ->with(7200);

                return $callback($item);
            });

        $upstreamMethods = [];
        foreach ($upstreamMethodsData as $methodData) {
            $method = $this->createMock(TestMethodInterface::class);
            $method->expects($this->atLeastOnce())
                ->method('getMethodId')
                ->willReturn($methodData['methodId']);
            $method->expects($this->any())
                ->method('getMinimumAmount')
                ->willReturn($methodData['minimumAmount'] ?? []);
            $method->expects($this->any())
                ->method('getMaximumAmount')
                ->willReturn($methodData['maximumAmount']);
            $upstreamMethods[$methodData['methodId']] = $method;
        }

        $this->setupUpstreamMethodsWithMocks($gateway, $upstreamMethods);

        $existingMethodMocks = [];
        foreach ($existingMethods as $methodData) {
            $mock = $this->createMock(MollieGatewayConfigInterface::class);
            $mock->expects($this->atLeastOnce())
                ->method('getMethodId')
                ->willReturn($methodData['methodId']);
            $mock->expects($this->atLeastOnce())
                ->method('getPosition')
                ->willReturn($methodData['position']);

            if (isset($expectedUpdates[$methodData['methodId']])) {
                $update = $expectedUpdates[$methodData['methodId']];
                $mock->expects($this->once())
                    ->method('setMinimumAmount')
                    ->with($update['minimumAmount']);
                $mock->expects($this->once())
                    ->method('setMaximumAmount')
                    ->with($update['maximumAmount']);
            } else {
                $mock->expects($this->never())->method('setMinimumAmount');
                $mock->expects($this->never())->method('setMaximumAmount');
            }

            $existingMethodMocks[$methodData['methodId']] = $mock;
        }

        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['gateway' => $gateway])
            ->willReturn(array_values($existingMethodMocks));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->updater->update($gateway);
    }

    public static function methodUpdateDataProvider(): \Generator
    {
        yield 'updates existing methods with new amounts' => [
            'upstreamMethodsData' => [
                [
                    'methodId' => 'creditcard',
                    'minimumAmount' => ['EUR' => 10],
                    'maximumAmount' => ['EUR' => 5000],
                ],
                [
                    'methodId' => 'ideal',
                    'minimumAmount' => [],
                    'maximumAmount' => ['EUR' => 50000],
                ],
            ],
            'existingMethods' => [
                ['methodId' => 'creditcard', 'position' => 1],
                ['methodId' => 'ideal', 'position' => 2],
            ],
            'expectedUpdates' => [
                'creditcard' => [
                    'minimumAmount' => ['EUR' => 10],
                    'maximumAmount' => ['EUR' => 5000],
                ],
                'ideal' => [
                    'minimumAmount' => [],
                    'maximumAmount' => ['EUR' => 50000],
                ],
            ],
        ];
    }

    /** @dataProvider newMethodCreationDataProvider */
    public function testCreatesNewMethodsFromUpstream(
        array $upstreamMethodsData,
        array $existingMethods,
        array $expectedNewMethods,
    ): void {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(333);

        $this->cache->expects($this->exactly(2))
            ->method('get')
            ->with('sylius_mollie_methods_333', $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return false;
                }

                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())
                    ->method('expiresAfter')
                    ->with(7200);

                return $callback($item);
            });

        $upstreamMethods = [];
        foreach ($upstreamMethodsData as $methodData) {
            $method = $this->createMock(TestMethodInterface::class);
            $method->expects($this->atLeastOnce())
                ->method('getMethodId')
                ->willReturn($methodData['methodId']);
            $upstreamMethods[$methodData['methodId']] = $method;
        }

        $this->setupUpstreamMethodsWithMocks($gateway, $upstreamMethods);

        $existingMethodMocks = [];
        foreach ($existingMethods as $methodData) {
            $mock = $this->createMock(MollieGatewayConfigInterface::class);
            $mock->expects($this->atLeastOnce())
                ->method('getMethodId')
                ->willReturn($methodData['methodId']);
            $mock->expects($this->atLeastOnce())
                ->method('getPosition')
                ->willReturn($methodData['position']);
            $existingMethodMocks[$methodData['methodId']] = $mock;
        }

        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['gateway' => $gateway])
            ->willReturn(array_values($existingMethodMocks));

        $createCallCount = count($expectedNewMethods);
        $persistCallCount = count($expectedNewMethods);

        if ($createCallCount > 0) {
            $this->mollieGatewayConfigFactory->expects($this->exactly($createCallCount))
                ->method('create');

            $this->entityManager->expects($this->exactly($persistCallCount))
                ->method('persist');
        }

        foreach ($expectedNewMethods as $expectedMethod) {
            $newMethodMock = $this->createMock(MollieGatewayConfigInterface::class);
        }

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->updater->update($gateway);
    }

    public static function newMethodCreationDataProvider(): \Generator
    {
        yield 'creates new methods when none exist' => [
            'upstreamMethodsData' => [
                ['methodId' => 'creditcard'],
                ['methodId' => 'ideal'],
            ],
            'existingMethods' => [],
            'expectedNewMethods' => [
                ['methodId' => 'creditcard', 'position' => 0],
                ['methodId' => 'ideal', 'position' => 1],
            ],
        ];

        yield 'creates only new methods not in existing' => [
            'upstreamMethodsData' => [
                ['methodId' => 'creditcard'],
                ['methodId' => 'ideal'],
                ['methodId' => 'bancontact'],
            ],
            'existingMethods' => [
                ['methodId' => 'creditcard', 'position' => 1],
            ],
            'expectedNewMethods' => [
                ['methodId' => 'ideal', 'position' => 2],
                ['methodId' => 'bancontact', 'position' => 3],
            ],
        ];

        yield 'calculates position based on existing max position' => [
            'upstreamMethodsData' => [
                ['methodId' => 'paypal'],
            ],
            'existingMethods' => [
                ['methodId' => 'creditcard', 'position' => 5],
                ['methodId' => 'ideal', 'position' => 2],
                ['methodId' => 'bancontact', 'position' => 10],
            ],
            'expectedNewMethods' => [
                ['methodId' => 'paypal', 'position' => 11],
            ],
        ];
    }

    /** @dataProvider positionCalculationDataProvider */
    public function testPositionCalculationWithNullPositions(
        array $existingMethods,
        int $expectedStartPosition,
    ): void {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(444);

        $this->cache->expects($this->exactly(2))
            ->method('get')
            ->with('sylius_mollie_methods_444', $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return false;
                }

                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())
                    ->method('expiresAfter')
                    ->with(7200);

                return $callback($item);
            });

        $upstreamMethod = $this->createMock(TestMethodInterface::class);
        $upstreamMethod->expects($this->atLeastOnce())
            ->method('getMethodId')
            ->willReturn('new_method');

        $upstreamMethods = ['new_method' => $upstreamMethod];
        $this->setupUpstreamMethodsWithMocks($gateway, $upstreamMethods);

        $existingMethodMocks = [];
        foreach ($existingMethods as $methodData) {
            $mock = $this->createMock(MollieGatewayConfigInterface::class);
            $mock->expects($this->atLeastOnce())
                ->method('getMethodId')
                ->willReturn($methodData['methodId']);
            $mock->expects($this->atLeastOnce())
                ->method('getPosition')
                ->willReturn($methodData['position']);
            $existingMethodMocks[] = $mock;
        }

        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['gateway' => $gateway])
            ->willReturn($existingMethodMocks);

        $newMethodMock = $this->createMock(MollieGatewayConfigInterface::class);
        $this->mollieGatewayConfigFactory->expects($this->once())
            ->method('create')
            ->with($upstreamMethod, $gateway, $expectedStartPosition)
            ->willReturn($newMethodMock);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($newMethodMock);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->updater->update($gateway);
    }

    public static function positionCalculationDataProvider(): \Generator
    {
        yield 'handles null positions correctly' => [
            'existingMethods' => [
                ['methodId' => 'method1', 'position' => null],
                ['methodId' => 'method2', 'position' => 5],
                ['methodId' => 'method3', 'position' => null],
            ],
            'expectedStartPosition' => 6,
        ];

        yield 'starts from 0 when all positions are null' => [
            'existingMethods' => [
                ['methodId' => 'method1', 'position' => null],
                ['methodId' => 'method2', 'position' => null],
            ],
            'expectedStartPosition' => 0,
        ];
    }

    /** @dataProvider cacheKeyDataProvider */
    public function testCacheKeyGeneration(int $gatewayId, string $expectedCacheKey): void
    {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn($gatewayId);

        $this->cache->expects($this->once())
            ->method('get')
            ->with($expectedCacheKey, $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) {
                return true;
            });

        $this->updater->update($gateway);
    }

    public static function cacheKeyDataProvider(): \Generator
    {
        yield 'generates correct cache key for gateway ID 1' => [
            'gatewayId' => 1,
            'expectedCacheKey' => 'sylius_mollie_methods_1',
        ];

        yield 'generates correct cache key for gateway ID 999' => [
            'gatewayId' => 999,
            'expectedCacheKey' => 'sylius_mollie_methods_999',
        ];

        yield 'generates correct cache key for gateway ID 0' => [
            'gatewayId' => 0,
            'expectedCacheKey' => 'sylius_mollie_methods_0',
        ];
    }

    /** @dataProvider gatewayTypeFilteringDataProvider */
    public function testMethodFilteringBasedOnGatewayType(
        string $factoryName,
        array $providedMethods,
        array $expectedFilteredMethods,
    ): void {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(555);
        $gateway->expects($this->atLeastOnce())
            ->method('getFactoryName')
            ->willReturn($factoryName);

        $this->cache->expects($this->exactly(2))
            ->method('get')
            ->with('sylius_mollie_methods_555', $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return false;
                }

                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())
                    ->method('expiresAfter')
                    ->with(7200);

                return $callback($item);
            });

        $methods = $this->createMock(PaymentMethodRegistryInterface::class);
        $this->methodsFactory->expects($this->once())
            ->method('createNew')
            ->willReturn($methods);

        $methodMocks = [];
        foreach ($providedMethods as $methodId) {
            $methodMock = $this->createMock(Method::class);
            $methodMock->id = $methodId;
            $methodMocks[] = $methodMock;
        }

        $this->methodsProvider->expects($this->once())
            ->method('getMethods')
            ->with($gateway)
            ->willReturn($methodMocks);

        $addCalls = [];
        foreach ($expectedFilteredMethods as $expectedMethodId) {
            $addCalls[] = $this->callback(function ($method) use ($expectedMethodId) {
                return $method->id === $expectedMethodId;
            });
        }

        $methods
            ->expects($this->exactly(count($addCalls)))
            ->method('add');

        $enabledMethods = [];
        foreach ($expectedFilteredMethods as $methodId) {
            $enabledMethod = $this->createMock(TestMethodInterface::class);
            $enabledMethod->expects($this->once())
                ->method('getMethodId')
                ->willReturn($methodId);
            $enabledMethods[] = $enabledMethod;
        }

        $methods->expects($this->once())
            ->method('getAllEnabled')
            ->willReturn($enabledMethods);

        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['gateway' => $gateway])
            ->willReturn([]);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->updater->update($gateway);
    }

    public static function gatewayTypeFilteringDataProvider(): \Generator
    {
        yield 'filters unsupported methods for regular gateway' => [
            'factoryName' => 'mollie',
            'providedMethods' => [
                'creditcard',
                'ideal',
                'inghomepay', // This should be filtered out as unsupported
                'paypal',
            ],
            'expectedFilteredMethods' => ['creditcard', 'ideal', 'paypal'],
        ];

        yield 'filters non-recurring methods for subscription gateway' => [
            'factoryName' => MollieSubscriptionGatewayFactory::FACTORY_NAME,
            'providedMethods' => [
                'creditcard', // Supported recurring method
                'directdebit', // Supported recurring method
                'ideal', // Initial recurring method
                'paypal', // Should be filtered out for subscription gateway
                'bancontact', // Initial recurring method
            ],
            'expectedFilteredMethods' => ['creditcard', 'directdebit', 'ideal', 'bancontact'],
        ];

        yield 'allows all supported methods for subscription gateway' => [
            'factoryName' => MollieSubscriptionGatewayFactory::FACTORY_NAME,
            'providedMethods' => [
                'directdebit',
                'creditcard',
                'ideal',
                'bancontact',
                'belfius',
                'eps',
            ],
            'expectedFilteredMethods' => ['directdebit', 'creditcard', 'ideal', 'bancontact', 'belfius', 'eps'],
        ];
    }

    public function testUsesCustomCacheTtl(): void
    {
        $customTtl = 3600;
        $updater = new MollieMethodsUpdater(
            $this->cache,
            $this->methodsProvider,
            $this->mollieGatewayConfigRepository,
            $this->mollieGatewayConfigFactory,
            $this->methodsFactory,
            $this->entityManager,
            $customTtl,
        );

        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getId')
            ->willReturn(666);

        $this->cache->expects($this->exactly(2))
            ->method('get')
            ->with('sylius_mollie_methods_666', $this->isType('callable'))
            ->willReturnCallback(function ($key, $callback) use ($customTtl) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return false;
                }

                $item = $this->createMock(ItemInterface::class);
                $item->expects($this->once())
                    ->method('expiresAfter')
                    ->with($customTtl);

                return $callback($item);
            });

        $this->setupUpstreamMethods($gateway, ['creditcard']);

        $this->mollieGatewayConfigRepository->expects($this->once())
            ->method('findBy')
            ->with(['gateway' => $gateway])
            ->willReturn([]);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updater->update($gateway);
    }

    private function setupEmptyUpstreamMethods(GatewayConfigInterface $gateway): void
    {
        $methods = $this->createMock(PaymentMethodRegistryInterface::class);
        $this->methodsFactory->expects($this->once())
            ->method('createNew')
            ->willReturn($methods);

        $this->methodsProvider->expects($this->once())
            ->method('getMethods')
            ->with($gateway)
            ->willReturn([]);

        $methods->expects($this->once())
            ->method('getAllEnabled')
            ->willReturn([]);
    }

    private function setupUpstreamMethods(GatewayConfigInterface $gateway, array $methodIds): void
    {
        $upstreamMethods = [];
        foreach ($methodIds as $methodId) {
            $method = $this->createMock(TestMethodInterface::class);
            $method->expects($this->once())
                ->method('getMethodId')
                ->willReturn($methodId);
            $upstreamMethods[] = $method;
        }

        $this->setupUpstreamMethodsWithMocks($gateway, array_combine($methodIds, $upstreamMethods));
    }

    private function setupUpstreamMethodsWithMocks(GatewayConfigInterface $gateway, array $upstreamMethods): void
    {
        $methods = $this->createMock(PaymentMethodRegistryInterface::class);
        $this->methodsFactory->expects($this->once())
            ->method('createNew')
            ->willReturn($methods);

        $this->methodsProvider->expects($this->once())
            ->method('getMethods')
            ->with($gateway)
            ->willReturn([]);

        $methods->expects($this->once())
            ->method('getAllEnabled')
            ->willReturn(array_values($upstreamMethods));
    }
}

interface TestMethodInterface extends MethodInterface
{
    public function getMethodId(): string;
}
