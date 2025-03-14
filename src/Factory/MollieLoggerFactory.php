<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\MollieLoggerInterface;
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
