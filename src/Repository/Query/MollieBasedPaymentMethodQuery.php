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

namespace Sylius\MolliePlugin\Repository\Query;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;

final readonly class MollieBasedPaymentMethodQuery implements MollieBasedPaymentMethodQueryInterface
{
    private const FACTORY_NAMES = [
        MollieGatewayFactory::FACTORY_NAME,
        MollieSubscriptionGatewayFactory::FACTORY_NAME,
    ];

    /** @param PaymentMethodRepositoryInterface&EntityRepository $paymentMethodRepository */
    public function __construct(
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
    }

    public function getAllExcludingCode(string $code): iterable
    {
        return $this->paymentMethodRepository->createQueryBuilder('o')
            ->innerJoin('o.gatewayConfig', 'gatewayConfig')
            ->where('gatewayConfig.factoryName in (:factoryName)')
            ->andWhere('o.code != :code')
            ->setParameter('factoryName', self::FACTORY_NAMES)
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getOneByChannelAndFactoryName(
        ChannelInterface $channel,
        string $factoryName,
    ): ?PaymentMethodInterface {
        return $this->paymentMethodRepository->createQueryBuilder('o')
            ->innerJoin('o.gatewayConfig', 'gatewayConfig')
            ->andWhere('o.enabled = true')
            ->andWhere(':channel MEMBER OF o.channels')
            ->andWhere('gatewayConfig.factoryName = :factoryName')
            ->setParameter('channel', $channel)
            ->setParameter('factoryName', $factoryName)
            ->addOrderBy('o.position')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }
}
