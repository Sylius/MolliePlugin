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

namespace spec\SyliusMolliePlugin\Logger;

use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Entity\MollieLoggerInterface;
use SyliusMolliePlugin\Factory\MollieLoggerFactoryInterface;
use SyliusMolliePlugin\Logger\MollieLoggerAction;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class MollieLoggerActionSpec extends ObjectBehavior
{
    function let(
        MollieLoggerFactoryInterface $loggerFactory,
        RepositoryInterface $repository,
        RepositoryInterface $gatewayRepository,
        MollieFactoryNameResolverInterface $mollieFactoryNameResolver
    ): void {
        $this->beConstructedWith(
            $loggerFactory,
            $repository,
            $gatewayRepository,
            $mollieFactoryNameResolver
        );
        $mollieFactoryNameResolver->resolve()->willReturn('mollie_subscription');
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieLoggerAction::class);
    }

    function it_should_implement_mollie_logger_action_interface(): void
    {
        $this->shouldImplement(MollieLoggerActionInterface::class);
    }

    function it_adds_log_when_log_everything_is_set(
        MollieLoggerFactoryInterface $loggerFactory,
        MollieLoggerInterface $logger,
        RepositoryInterface $repository,
        RepositoryInterface $gatewayRepository,
        GatewayConfigInterface $config
    ): void {
        $gatewayRepository->findOneBy(['factoryName' => 'mollie_subscription'])->willReturn($config);
        $logLevel = MollieLoggerActionInterface::NOTICE;
        $message = 'log_test_message';
        $errorCode = Response::HTTP_OK;
        $loggerFactory->create($message, $logLevel, $errorCode)->willReturn($logger);

        $config->getConfig()->willReturn([
            'loggerLevel' => 'undefined'
        ]);
        $this->addLog($message, $logLevel, $errorCode);
        $repository->add($logger)->shouldNotBeCalled();

        $config->getConfig()->willReturn([
            'loggerLevel' => 2
        ]);
        $this->addLog($message, $logLevel, $errorCode);
        $repository->add($logger)->shouldBeCalledOnce();
    }

    function it_adds_log_when_log_errors_is_set(
        MollieLoggerFactoryInterface $loggerFactory,
        MollieLoggerInterface $logger,
        RepositoryInterface $repository,
        RepositoryInterface $gatewayRepository,
        GatewayConfigInterface $config
    ): void {
        $logLevel = MollieLoggerActionInterface::ERROR;
        $message = 'log_test_message';
        $errorCode = Response::HTTP_OK;
        $loggerFactory->create($message, $logLevel, $errorCode)->willReturn($logger);

        $gatewayRepository->findOneBy(['factoryName' => 'mollie_subscription'])->willReturn($config);
        $config->getConfig()->willReturn([
            'loggerLevel' => 1
        ]);
        $repository->add($logger)->shouldBeCalledOnce();

        $this->addLog($message, $logLevel, $errorCode);
    }

    function it_cannot_adds_log(
        MollieLoggerFactoryInterface $loggerFactory,
        MollieLoggerInterface $logger,
        RepositoryInterface $repository,
        RepositoryInterface $gatewayRepository
    ): void {
        $gatewayRepository->findOneBy(['factoryName' => 'mollie_subscription'])->willReturn(null);
        $logLevel = MollieLoggerActionInterface::ERROR;
        $message = 'log_test_message';
        $errorCode = Response::HTTP_OK;
        $loggerFactory->create($message, $logLevel, $errorCode)->willReturn($logger);

        $this->addLog($message, $logLevel, $errorCode);
        $repository->add($logger)->shouldNotBeCalled();
    }

    function it_adds_negative_log_when_log_everything_is_set(
        MollieLoggerFactoryInterface $loggerFactory,
        MollieLoggerInterface $logger,
        RepositoryInterface $repository,
        RepositoryInterface $gatewayRepository,
        GatewayConfigInterface $config
    ): void {
        $gatewayRepository->findOneBy(['factoryName' => 'mollie_subscription'])->willReturn($config);
        $logLevel = MollieLoggerActionInterface::NOTICE;
        $message = 'log_test_negative_message';
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $loggerFactory->create($message, $logLevel, $errorCode)->willReturn($logger);

        $config->getConfig()->willReturn([
            'loggerLevel' => 'undefined'
        ]);
        $this->addLog($message, $logLevel, $errorCode);
        $repository->add($logger)->shouldNotBeCalled();

        $config->getConfig()->willReturn([
            'loggerLevel' => 2
        ]);
        $this->addLog($message, $logLevel, $errorCode);
        $repository->add($logger)->shouldBeCalledOnce();
    }

    function it_adds_negative_log_when_log_errors_is_set(
        MollieLoggerFactoryInterface $loggerFactory,
        MollieLoggerInterface $logger,
        RepositoryInterface $repository,
        RepositoryInterface $gatewayRepository,
        GatewayConfigInterface $config
    ): void {
        $logLevel = MollieLoggerActionInterface::ERROR;
        $message = 'log_test__negative_message';
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $loggerFactory->create($message, $logLevel, $errorCode)->willReturn($logger);

        $gatewayRepository->findOneBy(['factoryName' => 'mollie_subscription'])->willReturn($config);
        $config->getConfig()->willReturn([
            'loggerLevel' => 1
        ]);
        $this->addLog($message, $logLevel, $errorCode);
        $repository->add($logger)->shouldBeCalledOnce();
    }

    function it_cannot_adds_negative_log(
        MollieLoggerFactoryInterface $loggerFactory,
        MollieLoggerInterface $logger,
        RepositoryInterface $repository,
        RepositoryInterface $gatewayRepository,
        GatewayConfigInterface $config
    ): void {
        $gatewayRepository->findOneBy(['factoryName' => 'mollie_subscription'])->willReturn(null);
        $logLevel = MollieLoggerActionInterface::ERROR;
        $message = 'log_test__negative_message';
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $loggerFactory->create($message, $logLevel, $errorCode)->willReturn($logger);

        $this->addLog($message, $logLevel, $errorCode);
        $repository->add($logger)->shouldNotBeCalled();
    }
}
