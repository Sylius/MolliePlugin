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

namespace Sylius\MolliePlugin\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;

final class OrderRepository extends BaseOrderRepository implements OrderRepositoryInterface
{
    public function findAbandonedByDateTime(\DateTime $dateTime): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.paymentState = :paymentState')
            ->andWhere('o.state = :state')
            ->andWhere('o.createdAt <= :createdAt')
            ->andWhere('o.abandonedEmail = :abandonedEmail')
            ->setParameter('state', OrderInterface::STATE_NEW)
            ->setParameter('paymentState', OrderPaymentStates::STATE_AWAITING_PAYMENT)
            ->setParameter('createdAt', $dateTime)
            ->setParameter('abandonedEmail', false)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }
}
