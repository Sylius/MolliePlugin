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

namespace Tests\Sylius\MolliePlugin\Unit\Provider\Methods;

use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Api\Resources\Method;
use Mollie\Api\Resources\MethodCollection;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Provider\Methods\MollieMethodsProvider;
use Sylius\MolliePlugin\Provider\Methods\MollieMethodsProviderInterface;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;

final class MollieMethodsProviderTest extends TestCase
{
    private MollieApiClient $mollieApiClient;

    private MollieLoggerActionInterface $loggerAction;

    private MollieMethodsProvider $mollieMethodsProvider;

    protected function setUp(): void
    {
        $this->mollieApiClient = $this->createMock(MollieApiClient::class);
        $this->loggerAction = $this->createMock(MollieLoggerActionInterface::class);
        $this->mollieMethodsProvider = new MollieMethodsProvider(
            $this->mollieApiClient,
            $this->loggerAction,
        );
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(MollieMethodsProviderInterface::class, $this->mollieMethodsProvider);
    }

    /** @dataProvider regularGatewayDataProvider */
    public function testGetMethodsForRegularGateway(
        bool $environment,
        array $config,
        string $expectedApiKey,
        bool $expectedRecurring,
        array $expectedResult,
    ): void {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->atLeastOnce())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $gateway->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn($config);

        $this->mollieApiClient->expects($this->once())
            ->method('setApiKey')
            ->with($expectedApiKey)
            ->willReturnSelf();

        $this->mollieApiClient->expects($this->once())
            ->method('setIsRecurringSubscription')
            ->with($expectedRecurring);

        $this->setupRegularMethodsCall($expectedResult);

        $this->loggerAction->expects($this->never())
            ->method('addLog');

        $result = $this->mollieMethodsProvider->getMethods($gateway);

        $this->assertCount(count($expectedResult), $result);

        foreach ($expectedResult as $index => $expectedMethod) {
            $this->assertInstanceOf(Method::class, $result[$index]);
            foreach ($expectedMethod as $property => $value) {
                $this->assertEquals($value, $result[$index]->$property);
            }
        }
    }

    public static function regularGatewayDataProvider(): array
    {
        return [
            'live environment no recurring config' => [
                true,
                [
                    'environment' => true,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_123',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_456',
                ],
                'live_key_123',
                false,
                [['id' => 'creditcard', 'status' => 'activated'], ['id' => 'ideal', 'status' => 'activated']],
            ],
            'test environment no recurring config' => [
                false,
                [
                    'environment' => false,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_123',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_456',
                ],
                'test_key_456',
                false,
                [['id' => 'bancontact', 'status' => 'activated']],
            ],
            'live environment with partial recurring config (times only)' => [
                true,
                [
                    'environment' => true,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_789',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_789',
                    'times' => 5,
                ],
                'live_key_789',
                false,
                [['id' => 'sofort', 'status' => 'activated']],
            ],
            'test environment with partial recurring config (interval only)' => [
                false,
                [
                    'environment' => false,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_999',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_999',
                    'interval' => 'month',
                ],
                'test_key_999',
                false,
                [['id' => 'paypal', 'status' => 'activated']],
            ],
            'live environment with full recurring config (should be false for regular gateway)' => [
                true,
                [
                    'environment' => true,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_recurring',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_recurring',
                    'times' => 10,
                    'interval' => 'week',
                ],
                'live_key_recurring',
                true,
                [['id' => 'creditcard', 'status' => 'activated'], ['id' => 'directdebit', 'status' => 'activated']],
            ],
        ];
    }

    /** @dataProvider subscriptionGatewayDataProvider */
    public function testGetMethodsForSubscriptionGateway(
        bool $environment,
        array $config,
        string $expectedApiKey,
        bool $expectedRecurring,
        array $expectedResult,
    ): void {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->atLeastOnce())
            ->method('getFactoryName')
            ->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);

        $gateway->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn($config);

        $this->mollieApiClient->expects($this->once())
            ->method('setApiKey')
            ->with($expectedApiKey)
            ->willReturnSelf();

        $this->mollieApiClient->expects($this->once())
            ->method('setIsRecurringSubscription')
            ->with($expectedRecurring);

        $this->setupSubscriptionMethodsCall($expectedResult);

        $this->loggerAction->expects($this->never())
            ->method('addLog');

        $result = $this->mollieMethodsProvider->getMethods($gateway);

        $this->assertCount(count($expectedResult), $result);

        foreach ($expectedResult as $index => $expectedMethod) {
            $this->assertInstanceOf(Method::class, $result[$index]);
            foreach ($expectedMethod as $property => $value) {
                $this->assertEquals($value, $result[$index]->$property);
            }
        }
    }

    public static function subscriptionGatewayDataProvider(): array
    {
        return [
            'live environment no recurring config' => [
                true,
                [
                    'environment' => true,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_sub_123',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_sub_123',
                ],
                'live_sub_123',
                false,
                [['id' => 'creditcard'], ['id' => 'directdebit']],
            ],
            'test environment no recurring config' => [
                false,
                [
                    'environment' => false,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_sub_456',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_sub_456',
                ],
                'test_sub_456',
                false,
                [['id' => 'ideal']],
            ],
            'live environment with full recurring config' => [
                true,
                [
                    'environment' => true,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_sub_recurring',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_sub_recurring',
                    'times' => 12,
                    'interval' => 'month',
                ],
                'live_sub_recurring',
                true,
                [['id' => 'creditcard'], ['id' => 'directdebit']],
            ],
            'test environment with full recurring config' => [
                false,
                [
                    'environment' => false,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_sub_789',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_sub_789',
                    'times' => 6,
                    'interval' => 'quarter',
                ],
                'test_sub_789',
                true,
                [['id' => 'ideal'], ['id' => 'sofort']],
            ],
            'live environment with partial recurring config (times only)' => [
                true,
                [
                    'environment' => true,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_sub_partial1',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_sub_partial1',
                    'times' => 3,
                ],
                'live_sub_partial1',
                false,
                [['id' => 'paypal']],
            ],
            'test environment with partial recurring config (interval only)' => [
                false,
                [
                    'environment' => false,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_sub_partial2',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_sub_partial2',
                    'interval' => 'year',
                ],
                'test_sub_partial2',
                false,
                [['id' => 'eps']],
            ],
        ];
    }

    /** @dataProvider unsupportedGatewayDataProvider */
    public function testGetMethodsForUnsupportedGateway(string $factoryName): void
    {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->once())
            ->method('getFactoryName')
            ->willReturn($factoryName);

        $gateway->expects($this->never())
            ->method('getGatewayName');

        $gateway->expects($this->never())
            ->method('getConfig');

        $this->mollieApiClient->expects($this->never())
            ->method('setApiKey');

        $this->mollieApiClient->expects($this->never())
            ->method('setIsRecurringSubscription');

        $this->loggerAction->expects($this->once())
            ->method('addLog')
            ->with(sprintf('Unable to download methods for "%s"', $factoryName));

        $result = $this->mollieMethodsProvider->getMethods($gateway);

        $this->assertEquals([], $result);
    }

    public static function unsupportedGatewayDataProvider(): array
    {
        return [
            'unknown factory' => [
                'unknown_factory',
            ],
            'stripe factory' => [
                'stripe',
            ],
            'paypal factory' => [
                'paypal_express',
            ],
            'offline factory' => [
                'offline',
            ],
            'empty factory name' => [
                '',
            ],
            'custom factory' => [
                'custom_payment',
            ],
        ];
    }

    public function testFetchRegularFiltersOnlyActivatedMethods(): void
    {
        $gateway = $this->createGatewayMock(MollieGatewayFactory::FACTORY_NAME);

        $methodEndpoint = $this->createMock(MethodEndpoint::class);
        $this->mollieApiClient->methods = $methodEndpoint;

        $allMethods = new MethodCollection(3, null);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $activatedMethod = new Method($mollieApiClientMock);
        $activatedMethod->status = 'activated';
        $activatedMethod->id = 'creditcard';

        $inactiveMethod = new Method($mollieApiClientMock);
        $inactiveMethod->status = 'inactive';
        $inactiveMethod->id = 'bancontact';

        $noStatusMethod = new Method($mollieApiClientMock);
        $noStatusMethod->id = 'ideal';

        $allMethods->append($activatedMethod);
        $allMethods->append($inactiveMethod);
        $allMethods->append($noStatusMethod);

        $methodEndpoint->expects($this->once())
            ->method('allAvailable')
            ->with(MollieMethodsResolverInterface::PARAMETERS_AVAILABLE)
            ->willReturn($allMethods);

        $result = $this->mollieMethodsProvider->getMethods($gateway);

        $this->assertCount(1, $result);
        $this->assertEquals('creditcard', $result[0]->id);
        $this->assertEquals('activated', $result[0]->status);
    }

    public function testFetchSubscriptionCombinesActiveAndRecurringMethods(): void
    {
        $gateway = $this->createGatewayMock(MollieSubscriptionGatewayFactory::FACTORY_NAME);

        $methodEndpoint = $this->createMock(MethodEndpoint::class);
        $this->mollieApiClient->methods = $methodEndpoint;

        $baseCollection = new MethodCollection(2, null);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $baseMethod = new Method($mollieApiClientMock);
        $baseMethod->id = 'creditcard';
        $baseCollection->append($baseMethod);

        $recurringCollection = new MethodCollection(1, null);
        $recurringMethod = new Method($mollieApiClientMock);
        $recurringMethod->id = 'directdebit';
        $recurringCollection->append($recurringMethod);

        $methodEndpoint->expects($this->exactly(2))
            ->method('allActive')
            ->willReturnCallback(function ($params) use ($baseCollection, $recurringCollection) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    $this->assertEquals(MollieMethodsResolverInterface::PARAMETERS, $params);

                    return $baseCollection;
                }

                $this->assertEquals(MollieMethodsResolverInterface::PARAMETERS_RECURRING, $params);

                return $recurringCollection;
            });

        $result = $this->mollieMethodsProvider->getMethods($gateway);

        $this->assertCount(2, $result);
        $this->assertEquals('creditcard', $result[0]->id);
        $this->assertEquals('directdebit', $result[1]->id);
    }

    /** @dataProvider clientConfigurationDataProvider */
    public function testGetClientConfiguration(
        bool $environment,
        array $config,
        string $expectedApiKey,
        bool $expectedRecurring,
    ): void {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->atLeastOnce())
            ->method('getFactoryName')
            ->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $gateway->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        $this->mollieApiClient->expects($this->once())
            ->method('setApiKey')
            ->with($expectedApiKey)
            ->willReturnSelf();

        $this->mollieApiClient->expects($this->once())
            ->method('setIsRecurringSubscription')
            ->with($expectedRecurring);

        $this->setupRegularMethodsCall([]);

        $this->mollieMethodsProvider->getMethods($gateway);
    }

    public static function clientConfigurationDataProvider(): array
    {
        return [
            'live environment with recurring config' => [
                true,
                [
                    'environment' => true,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_123',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_123',
                    'times' => 5,
                    'interval' => 'month',
                ],
                'live_key_123',
                true,
            ],
            'test environment without recurring config' => [
                false,
                [
                    'environment' => false,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_123',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_123',
                ],
                'test_key_123',
                false,
            ],
            'live environment without recurring config' => [
                true,
                [
                    'environment' => true,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_123',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_123',
                ],
                'live_key_123',
                false,
            ],
            'test environment with only times config' => [
                false,
                [
                    'environment' => false,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_123',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_123',
                    'times' => 5,
                ],
                'test_key_123',
                false,
            ],
            'test environment with only interval config' => [
                false,
                [
                    'environment' => false,
                    MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_123',
                    MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_123',
                    'interval' => 'month',
                ],
                'test_key_123',
                false,
            ],
        ];
    }

    private function createGatewayMock(string $factoryName): GatewayConfigInterface
    {
        $gateway = $this->createMock(GatewayConfigInterface::class);
        $gateway->expects($this->atLeastOnce())
            ->method('getFactoryName')
            ->willReturn($factoryName);

        $config = [
            'environment' => true,
            MollieGatewayConfigurationType::API_KEY_LIVE => 'live_key_123',
            MollieGatewayConfigurationType::API_KEY_TEST => 'test_key_123',
        ];

        $gateway->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn($config);

        $this->mollieApiClient->expects($this->atLeastOnce())
            ->method('setApiKey')
            ->with('live_key_123')
            ->willReturnSelf();

        $this->mollieApiClient->expects($this->atLeastOnce())
            ->method('setIsRecurringSubscription')
            ->with(false);

        return $gateway;
    }

    private function setupRegularMethodsCall(array $expectedResult): void
    {
        $methodEndpoint = $this->createMock(MethodEndpoint::class);
        $this->mollieApiClient->methods = $methodEndpoint;

        $allMethods = new MethodCollection(count($expectedResult), null);

        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        foreach ($expectedResult as $methodData) {
            $method = new Method($mollieApiClientMock);
            foreach ($methodData as $key => $value) {
                $method->$key = $value;
            }
            $allMethods->append($method);
        }

        $methodEndpoint->expects($this->once())
            ->method('allAvailable')
            ->with(MollieMethodsResolverInterface::PARAMETERS_AVAILABLE)
            ->willReturn($allMethods);
    }

    private function setupSubscriptionMethodsCall(array $expectedResult): void
    {
        $methodEndpoint = $this->createMock(MethodEndpoint::class);
        $this->mollieApiClient->methods = $methodEndpoint;

        $baseCollection = new MethodCollection(1, null);
        $recurringCollection = new MethodCollection(1, null);

        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        if (count($expectedResult) > 0) {
            $baseMethod = new Method($mollieApiClientMock);
            foreach ($expectedResult[0] as $key => $value) {
                $baseMethod->$key = $value;
            }
            $baseCollection->append($baseMethod);
        }

        if (count($expectedResult) > 1) {
            $recurringMethod = new Method($mollieApiClientMock);
            foreach ($expectedResult[1] as $key => $value) {
                $recurringMethod->$key = $value;
            }
            $recurringCollection->append($recurringMethod);
        }

        $methodEndpoint->expects($this->exactly(2))
            ->method('allActive')
            ->willReturnCallback(function ($params) use ($baseCollection, $recurringCollection) {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    $this->assertEquals(MollieMethodsResolverInterface::PARAMETERS, $params);

                    return $baseCollection;
                }

                $this->assertEquals(MollieMethodsResolverInterface::PARAMETERS_RECURRING, $params);

                return $recurringCollection;
            });
    }
}
