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

namespace Sylius\MolliePlugin\Factory;

use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\MolliePlugin\Entity\MollieLoggerInterface;

final class MollieLoggerFactory implements MollieLoggerFactoryInterface
{
    public function __construct(private readonly FactoryInterface $factory)
    {
    }

    public function createNew(): MollieLoggerInterface
    {
        /** @var MollieLoggerInterface $loggerFactory */
        $loggerFactory = $this->factory->createNew();

        return $loggerFactory;
    }

    public function create(
        string $message,
        int $logLevel,
        int $errorCode,
    ): MollieLoggerInterface {
        $mollieLogger = $this->createNew();
        $mollieLogger->setDateTime(new \DateTime('now'));
        $mollieLogger->setLevel($logLevel);
        $mollieLogger->setErrorCode($errorCode);
        $mollieLogger->setMessage($message);

        return $mollieLogger;
    }
}
