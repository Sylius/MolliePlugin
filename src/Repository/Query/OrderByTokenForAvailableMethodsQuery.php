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
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;

final class OrderByTokenForAvailableMethodsQuery implements OrderByTokenForAvailableMethodsQueryInterface
{
    /** @param OrderRepositoryInterface&EntityRepository $orderRepository */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function getOrder(string $tokenValue): ?OrderInterface
    {
        return $this->orderRepository->createQueryBuilder('o')
            ->andWhere('o.tokenValue = :tokenValue')
            ->andWhere('o.state IN (:states)')
            ->setParameter('tokenValue', $tokenValue)
            ->setParameter('states', [
                OrderInterface::STATE_NEW,
                OrderInterface::STATE_CART,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
