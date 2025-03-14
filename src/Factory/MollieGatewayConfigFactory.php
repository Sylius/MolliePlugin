<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Payments\Methods\MethodInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class MollieGatewayConfigFactory implements MollieGatewayConfigFactoryInterface
{
    /** @var FactoryInterface */
    private $mollieGatewayConfigFactory;

    /** @var RepositoryInterface */
    private $repository;

    public function __construct(FactoryInterface $mollieGatewayConfigFactory, RepositoryInterface $repository)
    {
        $this->mollieGatewayConfigFactory = $mollieGatewayConfigFactory;
        $this->repository = $repository;
    }

    private function createNewOrUpdate(MethodInterface $method, GatewayConfigInterface $gateway): MollieGatewayConfigInterface
    {
        /** @var ?MollieGatewayConfigInterface $methodExist */
        $methodExist = $this->repository->findOneBy([
            /** @phpstan-ignore-next-line Not every class which implements MethodInterface returns the same type */
            'methodId' => $method->getMethodId(),
            'gateway' => $gateway,
        ]);

        /** @var MollieGatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $this->mollieGatewayConfigFactory->createNew();

        return null !== $methodExist ? $methodExist : $gatewayConfig;
    }

    public function create(
        MethodInterface $method,
        GatewayConfigInterface $gateway,
        int $key
    ): MollieGatewayConfigInterface {
        $mollieGatewayConfig = $this->createNewOrUpdate($method, $gateway);

        /** @phpstan-ignore-next-line Not every class which implements MethodInterface returns the same type */
        $mollieGatewayConfig->setMethodId($method->getMethodId());
        $mollieGatewayConfig->setName($method->getName());
        $mollieGatewayConfig->setMinimumAmount($method->getMinimumAmount());
        $mollieGatewayConfig->setMaximumAmount($method->getMaximumAmount());
        $mollieGatewayConfig->setImage($method->getImage());
        $mollieGatewayConfig->setGateway($gateway);
        $mollieGatewayConfig->setIssuers($method->getIssuers());
        $mollieGatewayConfig->setPaymentType($method->getPaymentType());
        $mollieGatewayConfig->setApplePayDirectButton(false);
        $mollieGatewayConfig->setPosition($key);

        return $mollieGatewayConfig;
    }

    public function createNew(): MollieGatewayConfigInterface
    {
        return $this->mollieGatewayConfigFactory->createNew();
    }
}
