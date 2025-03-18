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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Logger;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Entity\MollieLoggerInterface;
use SyliusMolliePlugin\Factory\MollieLoggerFactoryInterface;
use SyliusMolliePlugin\Logger\MollieLoggerAction;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Symfony\Component\HttpFoundation\Response;

final class MollieLoggerActionTest extends TestCase
{
    private MollieLoggerFactoryInterface $loggerFactoryMock;

    private RepositoryInterface $repositoryMock;

    private RepositoryInterface $gatewayRepositoryMock;

    private MollieFactoryNameResolverInterface $mollieFactoryNameResolverMock;

    private MollieLoggerAction $mollieLoggerAction;

    protected function setUp(): void
    {
        $this->loggerFactoryMock = $this->createMock(MollieLoggerFactoryInterface::class);
        $this->repositoryMock = $this->createMock(RepositoryInterface::class);
        $this->gatewayRepositoryMock = $this->createMock(RepositoryInterface::class);
        $this->mollieFactoryNameResolverMock = $this->createMock(MollieFactoryNameResolverInterface::class);

        $this->mollieLoggerAction = new MollieLoggerAction(
            $this->loggerFactoryMock,
            $this->repositoryMock,
            $this->gatewayRepositoryMock,
            $this->mollieFactoryNameResolverMock,
        );
    }

    public function testImplementMollieLoggerActionInterface(): void
    {
        $this->assertInstanceOf(MollieLoggerActionInterface::class, $this->mollieLoggerAction);
    }

    public function testAddsLogWhenLogEverythingIsSet(): void
    {
        $this->mollieFactoryNameResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('mollie_subscription')
        ;

        $loggerMock = $this->createMock(MollieLoggerInterface::class);
        $configMock = $this->createMock(GatewayConfigInterface::class);

        $this->gatewayRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['factoryName' => 'mollie_subscription'])
            ->willReturn($configMock)
        ;

        $logLevel = MollieLoggerActionInterface::NOTICE;
        $message = 'log_test_message';
        $errorCode = Response::HTTP_OK;

        $this->loggerFactoryMock->expects($this->once())
            ->method('create')
            ->with($message, $logLevel, $errorCode)
            ->willReturn($loggerMock)
        ;

        $configMock->expects($this->once())
            ->method('getConfig')
            ->willReturn(['loggerLevel' => MollieLoggerActionInterface::LOG_EVERYTHING])
        ;

        $this->repositoryMock->expects($this->once())
            ->method('add')
            ->with($loggerMock)
        ;

        $this->mollieLoggerAction->addLog($message, $logLevel, $errorCode);
    }

    public function testAddsLogWhenLogErrorsIsSet(): void
    {
        $this->mollieFactoryNameResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('mollie_subscription');

        $loggerMock = $this->createMock(MollieLoggerInterface::class);
        $configMock = $this->createMock(GatewayConfigInterface::class);

        $logLevel = MollieLoggerActionInterface::ERROR;
        $message = 'log_test_message';
        $errorCode = Response::HTTP_OK;

        $this->loggerFactoryMock->expects($this->once())
            ->method('create')
            ->with($message, $logLevel, $errorCode)
            ->willReturn($loggerMock)
        ;

        $this->gatewayRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['factoryName' => 'mollie_subscription'])
            ->willReturn($configMock)
        ;

        $configMock->expects($this->once())
            ->method('getConfig')
            ->willReturn(['loggerLevel' => 1])
        ;

        $this->repositoryMock->expects($this->once())
            ->method('add')
            ->with($loggerMock)
        ;

        $this->mollieLoggerAction->addLog($message, $logLevel, $errorCode);
    }

    public function testCannotAddsLog(): void
    {
        $this->mollieFactoryNameResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('mollie_subscription')
        ;

        $logLevel = MollieLoggerActionInterface::ERROR;
        $message = 'log_test_message';
        $errorCode = Response::HTTP_OK;

        $this->gatewayRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['factoryName' => 'mollie_subscription'])
            ->willReturn(null)
        ;

        $this->loggerFactoryMock->expects($this->never())
            ->method('create')
        ;

        $this->repositoryMock->expects($this->never())
            ->method('add')
        ;

        $this->mollieLoggerAction->addLog($message, $logLevel, $errorCode);
    }

    public function testAddsNegativeLogWhenLogEverythingIsSet(): void
    {
        $this->mollieFactoryNameResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('mollie_subscription');

        $loggerMock = $this->createMock(MollieLoggerInterface::class);
        $configMock = $this->createMock(GatewayConfigInterface::class);

        $this->gatewayRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['factoryName' => 'mollie_subscription'])
            ->willReturn($configMock)
        ;

        $logLevel = MollieLoggerActionInterface::NOTICE;
        $message = 'log_test_negative_message';
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        $this->loggerFactoryMock->expects($this->once())
            ->method('create')
            ->with($message, $logLevel, $errorCode)
            ->willReturn($loggerMock)
        ;

        $configMock->expects($this->once())
            ->method('getConfig')
            ->willReturn(['loggerLevel' => MollieLoggerActionInterface::LOG_EVERYTHING])
        ;

        $this->repositoryMock->expects($this->once())
            ->method('add')
            ->with($loggerMock)
        ;

        $this->mollieLoggerAction->addLog($message, $logLevel, $errorCode);
    }

    public function testAddsNegativeLogWhenLogErrorsIsSet(): void
    {
        $this->mollieFactoryNameResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('mollie_subscription')
        ;

        $loggerMock = $this->createMock(MollieLoggerInterface::class);
        $configMock = $this->createMock(GatewayConfigInterface::class);

        $logLevel = MollieLoggerActionInterface::ERROR;
        $message = 'log_test_negative_message';
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        $this->loggerFactoryMock->expects($this->once())
            ->method('create')
            ->with($message, $logLevel, $errorCode)
            ->willReturn($loggerMock)
        ;

        $this->gatewayRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['factoryName' => 'mollie_subscription'])
            ->willReturn($configMock)
        ;

        $configMock->expects($this->once())
            ->method('getConfig')
            ->willReturn(['loggerLevel' => 1])
        ;

        $this->repositoryMock->expects($this->once())
            ->method('add')
            ->with($loggerMock)
        ;

        $this->mollieLoggerAction->addLog($message, $logLevel, $errorCode);
    }

    public function testCannotAddsNegativeLog(): void
    {
        $this->mollieFactoryNameResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('mollie_subscription')
        ;

        $logLevel = MollieLoggerActionInterface::ERROR;
        $message = 'log_test_negative_message';
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        $this->gatewayRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['factoryName' => 'mollie_subscription'])
            ->willReturn(null)
        ;

        $this->loggerFactoryMock->expects($this->never())
            ->method('create')
        ;

        $this->repositoryMock->expects($this->never())
            ->method('add')
        ;

        $this->mollieLoggerAction->addLog($message, $logLevel, $errorCode);
    }
}