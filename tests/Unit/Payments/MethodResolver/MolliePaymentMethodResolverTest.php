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

namespace Tests\Sylius\MolliePlugin\Unit\Payments\MethodResolver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Filter\MollieMethodFilterInterface;
use Sylius\MolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Sylius\MolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Sylius\MolliePlugin\Resolver\PaymentMethodResolver;

final class MolliePaymentMethodResolverTest extends TestCase
{
    private PaymentMethodsResolverInterface $decoratedServiceMock;

    private PaymentMethodRepositoryInterface $paymentMethodRepositoryMock;

    private MollieFactoryNameResolverInterface $factoryNameResolverMock;

    private MollieMethodFilterInterface $mollieMethodFilterMock;

    private EntityManagerInterface $entityManagerMock;

    private PaymentMethodResolver $molliePaymentMethodResolver;

    protected function setUp(): void
    {
        $this->decoratedServiceMock = $this->createMock(PaymentMethodsResolverInterface::class);
        $this->paymentMethodRepositoryMock = $this->createMock(PaymentMethodRepositoryInterface::class);
        $this->factoryNameResolverMock = $this->createMock(MollieFactoryNameResolverInterface::class);
        $this->mollieMethodFilterMock = $this->createMock(MollieMethodFilterInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->molliePaymentMethodResolver = new PaymentMethodResolver(
            $this->decoratedServiceMock,
            $this->paymentMethodRepositoryMock,
            $this->factoryNameResolverMock,
            $this->mollieMethodFilterMock,
            $this->entityManagerMock,
        );
    }

    public function testImplementPaymentMethodsResolverInterface(): void
    {
        $this->assertInstanceOf(PaymentMethodsResolverInterface::class, $this->molliePaymentMethodResolver);
    }

    public function testGetsSupportedMethodsWhenMethodIsNotNull(): void
    {
        $subjectMock = $this->createMock(PaymentInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $channelMock = $this->createMock(ChannelInterface::class);
        $methodMock = $this->createMock(PaymentMethodInterface::class);
        $subjectMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getChannel')->willReturn($channelMock);

        $factoryName = 'mollie_subscription';
        $this->factoryNameResolverMock->expects($this->once())->method('resolve')->with($orderMock)->willReturn($factoryName);
        $this->paymentMethodRepositoryMock->expects($this->once())->method('findOneByChannelAndGatewayFactoryName')->with($channelMock, $factoryName)->willReturn($methodMock);

        $this->assertSame([$methodMock], $this->molliePaymentMethodResolver->getSupportedMethods($subjectMock));
    }

    public function testGetsSupportedMethodsAndHasNotRecurringContents(): void
    {
        $subjectMock = $this->createMock(PaymentInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $channelMock = $this->createMock(ChannelInterface::class);
        $methodMock = $this->createMock(PaymentMethodInterface::class);
        $parentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $parentMethodMock->method('getPosition')->willReturn(1);

        $channelMock->expects($this->any())
            ->method('getId')
            ->willReturn(1)
        ;

        $connectionMock = $this->createMock(Connection::class);
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $resultMock = $this->createMock(Result::class);

        $this->entityManagerMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock)
        ;

        $connectionMock->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock)
        ;

        $queryBuilderMock->expects($this->once())
            ->method('select')
            ->with('1')
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->once())
            ->method('from')
            ->with('sylius_payment_method_channels')
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->once())
            ->method('where')
            ->with('payment_method_id = :methodId')
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->once())
            ->method('andWhere')
            ->with('channel_id = :channelId')
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['methodId', $parentMethodMock->getId()],
                ['channelId', 1],
            )
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->once())
            ->method('executeQuery')
            ->willReturn($resultMock)
        ;

        $resultMock->method('fetchOne')
            ->willReturn(true)
        ;

        $subjectMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock)
        ;

        $orderMock->expects($this->once())
            ->method('getChannel')
            ->willReturn($channelMock)
        ;

        $factoryName = 'not_mollie_subscription';
        $this->factoryNameResolverMock->expects($this->once())
            ->method('resolve')
            ->with($orderMock)
            ->willReturn($factoryName)
        ;

        $this->paymentMethodRepositoryMock->expects($this->once())
            ->method('findOneByChannelAndGatewayFactoryName')
            ->with($channelMock, $factoryName)
            ->willReturn($methodMock)
        ;

        $this->decoratedServiceMock->expects($this->once())
            ->method('getSupportedMethods')
            ->with($subjectMock)
            ->willReturn([$parentMethodMock])
        ;

        $orderMock->expects($this->once())
            ->method('hasRecurringContents')
            ->willReturn(false)
        ;

        $this->mollieMethodFilterMock->expects($this->once())
            ->method('nonRecurringFilter')
            ->with([$parentMethodMock])
            ->willReturn([$parentMethodMock])
        ;

        $this->assertSame([1 => $parentMethodMock], $this->molliePaymentMethodResolver->getSupportedMethods($subjectMock));
    }

    public function testGetsSupportedMethodsAndHasRecurringContents(): void
    {
        $subjectMock = $this->createMock(PaymentInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);
        $channelMock = $this->createMock(ChannelInterface::class);
        $methodMock = $this->createMock(PaymentMethodInterface::class);
        $parentMethodMock = $this->createMock(PaymentMethodInterface::class);
        $parentMethodMock->method('getPosition')->willReturn(0);

        $channelMock->expects($this->any())
            ->method('getId')
            ->willReturn(1)
        ;

        $connectionMock = $this->createMock(Connection::class);
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $resultMock = $this->createMock(Result::class);

        $this->entityManagerMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock)
        ;

        $connectionMock->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock)
        ;

        $queryBuilderMock->expects($this->once())
            ->method('select')
            ->with('1')
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->once())
            ->method('from')
            ->with('sylius_payment_method_channels')
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->once())
            ->method('where')
            ->with('payment_method_id = :methodId')
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->once())
            ->method('andWhere')
            ->with('channel_id = :channelId')
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['methodId', $parentMethodMock->getId()],
                ['channelId', 1],
            )
            ->willReturnSelf()
        ;

        $queryBuilderMock->expects($this->once())
            ->method('executeQuery')
            ->willReturn($resultMock)
        ;

        $resultMock->method('fetchOne')->willReturn(true);

        $subjectMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock)
        ;

        $orderMock->expects($this->once())
            ->method('getChannel')
            ->willReturn($channelMock)
        ;

        $factoryName = 'not_mollie_subscription';
        $this->factoryNameResolverMock->expects($this->once())
            ->method('resolve')
            ->with($orderMock)
            ->willReturn($factoryName)
        ;

        $this->paymentMethodRepositoryMock->expects($this->once())
            ->method('findOneByChannelAndGatewayFactoryName')
            ->with($channelMock, $factoryName)
            ->willReturn($methodMock)
        ;

        $this->decoratedServiceMock->expects($this->once())
            ->method('getSupportedMethods')
            ->with($subjectMock)
            ->willReturn([$parentMethodMock])
        ;

        $orderMock->expects($this->once())
            ->method('hasRecurringContents')
            ->willReturn(true)
        ;

        $this->mollieMethodFilterMock->expects($this->once())
            ->method('recurringFilter')
            ->with([$parentMethodMock])
            ->willReturn([$parentMethodMock])
        ;

        $this->assertSame([$parentMethodMock], $this->molliePaymentMethodResolver->getSupportedMethods($subjectMock));
    }

    public function testSupports(): void
    {
        $subjectMock = $this->createMock(PaymentInterface::class);
        $orderMock = $this->createMock(OrderInterface::class);

        $subjectMock->expects($this->exactly(3))
        ->method('getOrder')
            ->willReturn($orderMock)
        ;

        $orderMock->expects($this->once())
            ->method('hasRecurringContents')
            ->willReturn(false)
        ;
        $orderMock->expects($this->once())
            ->method('hasNonRecurringContents')
            ->willReturn(true)
        ;
        $orderMock->expects($this->once())
            ->method('getChannel')
            ->willReturn(null)
        ;

        $this->assertFalse($this->molliePaymentMethodResolver->supports($subjectMock));
    }
}
