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

namespace Tests\Sylius\MolliePlugin\Unit\Resolver;

use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Api\Resources\MethodCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Creator\MollieMethodsCreatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolver;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;

final class MollieMethodsResolverTest extends TestCase
{
    private MollieLoggerActionInterface $loggerActionMock;

    private MollieApiClient $mollieApiClientMock;

    private RepositoryInterface $gatewayConfigRepositoryMock;

    private MollieMethodsCreatorInterface $helperMock;

    private MollieMethodsResolver $mollieMethodsResolver;

    protected function setUp(): void
    {
        $this->loggerActionMock = $this->createMock(MollieLoggerActionInterface::class);
        $this->mollieApiClientMock = $this->createMock(MollieApiClient::class);
        $this->gatewayConfigRepositoryMock = $this->createMock(RepositoryInterface::class);
        $this->helperMock = $this->createMock(MollieMethodsCreatorInterface::class);

        $this->mollieMethodsResolver = new MollieMethodsResolver(
            $this->loggerActionMock,
            $this->mollieApiClientMock,
            $this->gatewayConfigRepositoryMock,
            $this->helperMock,
        );
    }

    public function testImplementsMollieMethodsResolverInterface(): void
    {
        $this->assertInstanceOf(MollieMethodsResolverInterface::class, $this->mollieMethodsResolver);
    }

    public function testCreatesMollieMethodsForGatewaySubscription(): void
    {
        $gatewayMock = $this->createMock(GatewayConfigInterface::class);
        $clientMock = $this->createMock(MollieApiClient::class);
        $methodEndpointMock = $this->createMock(MethodEndpoint::class);

        $gatewayMock->method('getConfig')->willReturn([
            'environment' => 'test',
            'api_key_test' => 'test_key123',
            'times' => '5',
            'interval' => '12 months',
        ]);

        $this->mollieApiClientMock->method('setApiKey')->with('test_key123')->willReturn($clientMock);
        $gatewayMock->method('getFactoryName')->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $clientMock->methods = $methodEndpointMock;

        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $methodCollectionMock->method('getArrayCopy')->willReturn([]);
        $methodEndpointMock->method('allActive')->willReturn($methodCollectionMock);

        $this->helperMock->expects($this->once())->method('createMethods')->with($methodCollectionMock, $gatewayMock);
        $this->loggerActionMock->expects($this->once())->method('addLog')->with('Downloaded all methods from mollie API');

        $this->mollieMethodsResolver->createForGateway($gatewayMock);
    }

    public function testCreatesMollieMethodsForGatewayNonSubscription(): void
    {
        $gatewayMock = $this->createMock(GatewayConfigInterface::class);
        $clientMock = $this->createMock(MollieApiClient::class);
        $methodEndpointMock = $this->createMock(MethodEndpoint::class);

        $gatewayMock->method('getConfig')->willReturn([
            'environment' => 'test',
            'api_key_test' => 'test_key123',
            'times' => '5',
            'interval' => '12 months',
        ]);

        $this->mollieApiClientMock->method('setApiKey')->with('test_key123')->willReturn($clientMock);
        $gatewayMock->method('getFactoryName')->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $clientMock->methods = $methodEndpointMock;

        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $methodEndpointMock->method('allAvailable')->willReturn($methodCollectionMock);
        $methodCollectionMock->method('getArrayCopy')->willReturn([]);

        $this->helperMock->expects($this->once())->method('createMethods')->with($methodCollectionMock, $gatewayMock);
        $this->loggerActionMock->expects($this->once())->method('addLog')->with('Downloaded all methods from mollie API');

        $this->mollieMethodsResolver->createForGateway($gatewayMock);
    }

    public function testCreatesMollieMethodsForGatewayError(): void
    {
        $gatewayMock = $this->createMock(GatewayConfigInterface::class);
        $clientMock = $this->createMock(MollieApiClient::class);

        $gatewayMock->method('getConfig')->willReturn([
            'environment' => 'test',
            'api_key_test' => 'test_key123',
            'times' => '5',
            'interval' => '12 months',
        ]);

        $this->mollieApiClientMock->method('setApiKey')->with('test_key123')->willReturn($clientMock);
        $gatewayMock->method('getFactoryName')->willReturn('not_mollie_for_sure');
        $gatewayMock->method('getGatewayName')->willReturn('not_mollie');

        $this->loggerActionMock->expects($this->once())
            ->method('addLog')
            ->with('Unable to download methods for "not_mollie"')
        ;

        $this->mollieMethodsResolver->createForGateway($gatewayMock);
    }
}
