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

namespace SyliusMolliePlugin\Logger;

use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Factory\MollieLoggerFactoryInterface;
use SyliusMolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class MollieLoggerAction implements MollieLoggerActionInterface
{
    /** @var MollieLoggerFactoryInterface */
    private $loggerFactory;

    /** @var RepositoryInterface */
    private $repository;

    /** @var RepositoryInterface */
    private $gatewayRepository;

    /** @var MollieFactoryNameResolverInterface */
    private $mollieFactoryNameResolver;

    public function __construct(
        MollieLoggerFactoryInterface $loggerFactory,
        RepositoryInterface $repository,
        RepositoryInterface $gatewayRepository,
        MollieFactoryNameResolverInterface $mollieFactoryNameResolver
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->repository = $repository;
        $this->gatewayRepository = $gatewayRepository;
        $this->mollieFactoryNameResolver = $mollieFactoryNameResolver;
    }

    public function addLog(
        string $message,
        int $logLevel = self::NOTICE,
        int $errorCode = Response::HTTP_OK
    ): void {
        if (false === $this->canSaveLog($logLevel)) {
            return;
        }

        $logger = $this->loggerFactory->create($message, $logLevel, $errorCode);
        $this->repository->add($logger);
    }

    public function addNegativeLog(
        string $message,
        int $logLevel = self::ERROR,
        int $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR
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

            if (MollieLoggerActionInterface::LOG_EVERYTHING === $level) {
                return true;
            }

            if (MollieLoggerActionInterface::LOG_ERRORS === $level && self::ERROR === $logLevel) {
                return true;
            }

            return false;
        } catch (CartNotFoundException $e) {
            // As we cannot determine cart context (CLI context), we agree to store logs

            return true;
        }
    }
}
