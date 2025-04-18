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

namespace Tests\Sylius\MolliePlugin\Unit\Creator;

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Resources\Method;
use Mollie\Api\Resources\MethodCollection;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Creator\MollieMethodsCreator;
use Sylius\MolliePlugin\Creator\MollieMethodsCreatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Factory\MethodsFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayConfigFactoryInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\MethodInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Registry\PaymentMethodRegistryInterface;

final class MollieMethodsCreatorTest extends TestCase
{
    private MethodsFactoryInterface $methodsFactoryMock;

    private EntityManagerInterface $entityManagerMock;

    private MollieGatewayConfigFactoryInterface $factoryMock;

    private MollieMethodsCreator $mollieMethodsCreator;

    protected function setUp(): void
    {
        $this->methodsFactoryMock = $this->createMock(MethodsFactoryInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->factoryMock = $this->createMock(MollieGatewayConfigFactoryInterface::class);
        $this->mollieMethodsCreator = new MollieMethodsCreator($this->methodsFactoryMock, $this->entityManagerMock, $this->factoryMock);
    }

    public function testImplementInterface(): void
    {
        $this->assertInstanceOf(MollieMethodsCreatorInterface::class, $this->mollieMethodsCreator);
    }

    public function testCreatesMethodsWhenAllMollieMethodsAreEmpty(): void
    {
        $methodsMock = $this->createMock(PaymentMethodRegistryInterface::class);
        $methodMock = $this->createMock(MethodInterface::class);
        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieGatewayConfigMock = $this->createMock(MollieGatewayConfigInterface::class);

        $this->methodsFactoryMock->expects($this->once())->method('createNew')->willReturn($methodsMock);

        $methodsCollectionArray = [];
        $methodsCollectionArrayIterator = new \ArrayIterator($methodsCollectionArray);

        $methodCollectionMock->expects($this->once())->method('getIterator')->willReturn($methodsCollectionArrayIterator);

        $methodsMock->expects($this->once())->method('getAllEnabled')->willReturn([$methodMock]);
        $this->factoryMock->expects($this->once())->method('create')->with($methodMock, $gatewayConfigMock, 0)->willReturn($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->mollieMethodsCreator->createMethods($methodCollectionMock, $gatewayConfigMock);
    }

    public function testCreatesMethodsWhenAllMollieMethodsAreUnsupported(): void
    {
        $methodsMock = $this->createMock(PaymentMethodRegistryInterface::class);
        $methodMock = $this->createMock(MethodInterface::class);
        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieGatewayConfigMock = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $this->methodsFactoryMock->expects($this->once())->method('createNew')->willReturn($methodsMock);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'inghomepay';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayIterator($methodsCollectionArray);

        $methodCollectionMock->expects($this->once())->method('getIterator')->willReturn($methodsCollectionArrayIterator);

        $methodsMock->expects($this->never())->method('add')->with($mollieMethod);
        $methodsMock->expects($this->once())->method('getAllEnabled')->willReturn([$methodMock]);
        $this->factoryMock->expects($this->once())->method('create')->with($methodMock, $gatewayConfigMock, 0)->willReturn($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->mollieMethodsCreator->createMethods($methodCollectionMock, $gatewayConfigMock);
    }

    public function testCreatesMethodsWhenAllMollieMethodsAreNotUnsupportedAndFactoryNameIsNotSubscriptionAndSupportedIsFalseAndInitialIsTrue(): void
    {
        $methodsMock = $this->createMock(PaymentMethodRegistryInterface::class);
        $methodMock = $this->createMock(MethodInterface::class);
        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieGatewayConfigMock = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $this->methodsFactoryMock->expects($this->once())->method('createNew')->willReturn($methodsMock);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'bancontact';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayIterator($methodsCollectionArray);

        $gatewayConfigMock->expects($this->once())->method('getFactoryName')->willReturn('not_subscription');
        $methodCollectionMock->expects($this->once())->method('getIterator')->willReturn($methodsCollectionArrayIterator);

        $methodsMock->expects($this->once())->method('add')->with($mollieMethod);
        $methodsMock->expects($this->once())->method('getAllEnabled')->willReturn([$methodMock]);
        $this->factoryMock->expects($this->once())->method('create')->with($methodMock, $gatewayConfigMock, 0)->willReturn($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->mollieMethodsCreator->createMethods($methodCollectionMock, $gatewayConfigMock);
    }

    public function testCreatesMethodsWhenAllMollieMethodsAreSupportedAndFactoryNameIsNotSubscriptionAndBothSupportedAndInitialAreFalse(): void
    {
        $methodsMock = $this->createMock(PaymentMethodRegistryInterface::class);
        $methodMock = $this->createMock(MethodInterface::class);
        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieGatewayConfigMock = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $this->methodsFactoryMock->expects($this->once())->method('createNew')->willReturn($methodsMock);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'not_inghomepay';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayIterator($methodsCollectionArray);

        $gatewayConfigMock->expects($this->once())->method('getFactoryName')->willReturn('not_subscription');
        $methodCollectionMock->expects($this->once())->method('getIterator')->willReturn($methodsCollectionArrayIterator);

        $methodsMock->expects($this->once())->method('add')->with($mollieMethod);
        $methodsMock->expects($this->once())->method('getAllEnabled')->willReturn([$methodMock]);
        $this->factoryMock->expects($this->once())->method('create')->with($methodMock, $gatewayConfigMock, 0)->willReturn($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->mollieMethodsCreator->createMethods($methodCollectionMock, $gatewayConfigMock);
    }

    public function testCreatesMethodsWhenAllMollieMethodsAreSupportedAndFactoryNameIsNotSubscriptionAndSupportedIsTrueAndInitialIsFalse(): void
    {
        $methodsMock = $this->createMock(PaymentMethodRegistryInterface::class);
        $methodMock = $this->createMock(MethodInterface::class);
        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieGatewayConfigMock = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $this->methodsFactoryMock->expects($this->once())->method('createNew')->willReturn($methodsMock);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'directdebit';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayIterator($methodsCollectionArray);

        $gatewayConfigMock->expects($this->once())->method('getFactoryName')->willReturn('not_subscription');
        $methodCollectionMock->expects($this->once())->method('getIterator')->willReturn($methodsCollectionArrayIterator);

        $methodsMock->expects($this->once())->method('add')->with($mollieMethod);
        $methodsMock->expects($this->once())->method('getAllEnabled')->willReturn([$methodMock]);
        $this->factoryMock->expects($this->once())->method('create')->with($methodMock, $gatewayConfigMock, 0)->willReturn($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->mollieMethodsCreator->createMethods($methodCollectionMock, $gatewayConfigMock);
    }

    public function testCreatesMethodsWhenAllMollieMethodsAreSupportedAndFactoryNameIsSubscriptionAndSupportedIsFalseAndInitialIsTrue(): void
    {
        $methodsMock = $this->createMock(PaymentMethodRegistryInterface::class);
        $methodMock = $this->createMock(MethodInterface::class);
        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieGatewayConfigMock = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $this->methodsFactoryMock->expects($this->once())->method('createNew')->willReturn($methodsMock);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'bancontact';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayIterator($methodsCollectionArray);

        $gatewayConfigMock->expects($this->once())->method('getFactoryName')->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $methodCollectionMock->expects($this->once())->method('getIterator')->willReturn($methodsCollectionArrayIterator);

        $methodsMock->expects($this->once())->method('add')->with($mollieMethod);
        $methodsMock->expects($this->once())->method('getAllEnabled')->willReturn([$methodMock]);
        $this->factoryMock->expects($this->once())->method('create')->with($methodMock, $gatewayConfigMock, 0)->willReturn($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->mollieMethodsCreator->createMethods($methodCollectionMock, $gatewayConfigMock);
    }

    public function testCreatesMethodsWhenAllMollieMethodsAreSupportedAndFactoryNameIsSubscriptionAndBothSupportedAndInitialAreFalse(): void
    {
        $methodsMock = $this->createMock(PaymentMethodRegistryInterface::class);
        $methodMock = $this->createMock(MethodInterface::class);
        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieGatewayConfigMock = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $this->methodsFactoryMock->expects($this->once())->method('createNew')->willReturn($methodsMock);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'not_inghomepay';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayIterator($methodsCollectionArray);

        $gatewayConfigMock->expects($this->once())->method('getFactoryName')->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $methodCollectionMock->expects($this->once())->method('getIterator')->willReturn($methodsCollectionArrayIterator);

        $methodsMock->expects($this->never())->method('add')->with($mollieMethod);
        $methodsMock->expects($this->once())->method('getAllEnabled')->willReturn([$methodMock]);
        $this->factoryMock->expects($this->once())->method('create')->with($methodMock, $gatewayConfigMock, 0)->willReturn($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->mollieMethodsCreator->createMethods($methodCollectionMock, $gatewayConfigMock);
    }

    public function testCreatesMethodsWhenAllMollieMethodsAreSupportedAndFactoryNameIsSubscriptionAndSupportedIsTrueAndInitialIsFalse(): void
    {
        $methodsMock = $this->createMock(PaymentMethodRegistryInterface::class);
        $methodMock = $this->createMock(MethodInterface::class);
        $methodCollectionMock = $this->createMock(MethodCollection::class);
        $gatewayConfigMock = $this->createMock(GatewayConfigInterface::class);
        $mollieGatewayConfigMock = $this->createMock(MollieGatewayConfigInterface::class);
        $mollieApiClientMock = $this->createMock(MollieApiClient::class);

        $this->methodsFactoryMock->expects($this->once())->method('createNew')->willReturn($methodsMock);

        $mollieMethod = new Method($mollieApiClientMock);
        $mollieMethod->id = 'creditcard';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayIterator($methodsCollectionArray);

        $gatewayConfigMock->expects($this->once())->method('getFactoryName')->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $methodCollectionMock->expects($this->once())->method('getIterator')->willReturn($methodsCollectionArrayIterator);

        $methodsMock->expects($this->once())->method('add')->with($mollieMethod);
        $methodsMock->expects($this->once())->method('getAllEnabled')->willReturn([$methodMock]);
        $this->factoryMock->expects($this->once())->method('create')->with($methodMock, $gatewayConfigMock, 0)->willReturn($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('persist')->with($mollieGatewayConfigMock);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->mollieMethodsCreator->createMethods($methodCollectionMock, $gatewayConfigMock);
    }
}
