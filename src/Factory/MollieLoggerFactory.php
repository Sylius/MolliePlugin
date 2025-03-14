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

namespace SyliusMolliePlugin\Factory;

use SyliusMolliePlugin\Entity\MollieLoggerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class MollieLoggerFactory implements MollieLoggerFactoryInterface
{
    /** @var FactoryInterface */
    private $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
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
        int $errorCode
    ): MollieLoggerInterface {
        $mollieLogger = $this->createNew();
        $mollieLogger->setDateTime(new \DateTime('now'));
        $mollieLogger->setLevel($logLevel);
        $mollieLogger->setErrorCode($errorCode);
        $mollieLogger->setMessage($message);

        return $mollieLogger;
    }
}
