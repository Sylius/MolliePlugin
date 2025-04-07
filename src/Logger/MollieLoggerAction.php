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

namespace Sylius\MolliePlugin\Logger;

use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Factory\MollieLoggerFactoryInterface;
use Sylius\MolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Symfony\Component\HttpFoundation\Response;

final class MollieLoggerAction implements MollieLoggerActionInterface
{
    public function __construct(
        private readonly MollieLoggerFactoryInterface $loggerFactory,
        private readonly RepositoryInterface $repository,
        private readonly RepositoryInterface $gatewayRepository,
        private readonly MollieFactoryNameResolverInterface $mollieFactoryNameResolver,
    ) {
    }

    public function addLog(
        string $message,
        int $logLevel = LoggerLevel::NOTICE,
        int $errorCode = Response::HTTP_OK,
    ): void {
        if (false === $this->canSaveLog($logLevel)) {
            return;
        }

        $logger = $this->loggerFactory->create($message, $logLevel, $errorCode);
        $this->repository->add($logger);
    }

    public function addNegativeLog(
        string $message,
        int $logLevel = LoggerLevel::ERROR,
        int $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR,
    ): void {
        if (false === $this->canSaveLog($logLevel)) {
            return;
        }

        $logger = $this->loggerFactory->create($message, $logLevel, $errorCode);
        $this->repository->add($logger);
    }

    private function canSaveLog(int $logLevel): bool
    {
        try {
            /** @var ?GatewayConfigInterface $gatewayConfig */
            $gatewayConfig = $this->gatewayRepository->findOneBy(['factoryName' => $this->mollieFactoryNameResolver->resolve()]);

            if (null === $gatewayConfig) {
                // @todo - find better solution to resolve gateway
                return false;
            }

            $level = $gatewayConfig->getConfig()['loggerLevel'];

            if (LoggerLevel::LOG_EVERYTHING === $level) {
                return true;
            }

            if (LoggerLevel::LOG_ERRORS === $level && LoggerLevel::ERROR === $logLevel) {
                return true;
            }

            return false;
        } catch (CartNotFoundException) {
            // As we cannot determine cart context (CLI context), we agree to store logs

            return true;
        }
    }
}
