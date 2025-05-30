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

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

final class MollieSubscriptionRepository extends EntityRepository implements MollieSubscriptionRepositoryInterface
{
    public function findOneByOrderId(int $orderId): ?MollieSubscriptionInterface
    {
        try {
            $qb = $this->createQueryBuilder('q');

            $qb->leftJoin('q.orders', 'o');
            $qb->andWhere('o.id = :orderId');
            $qb->setParameter('orderId', $orderId);

            return $qb->getQuery()->getOneOrNullResult()
            ;
        } catch (\Exception) {
            return null;
        }
    }

    public function findByOrderId(int $orderId): array
    {
        $qb = $this->createQueryBuilder('q');

        $qb->leftJoin('q.orders', 'o');
        $qb->andWhere('o.id = :orderId');
        $qb->setParameter('orderId', $orderId);

        return $qb->getQuery()->getResult()
        ;
    }

    public function findByPayment(PaymentInterface $payment): array
    {
        $qb = $this->createQueryBuilder('q');
        $qb->andWhere(':payment MEMBER OF q.payments');
        $qb->setParameter('payment', $payment);

        return $qb->getQuery()->getResult();
    }

    public function findScheduledSubscriptions(): array
    {
        $qb = $this->createQueryBuilder('q');
        $qb->andWhere('q.state = :state');
        $qb->setParameter('state', MollieSubscriptionInterface::STATE_ACTIVE);
        $qb->leftJoin('q.schedules', 's');
        $qb->andWhere('s.scheduledDate < :date');
        $qb->setParameter('date', new \DateTime());
        $qb->andWhere('s.fulfilledDate IS NULL');

        return $qb->getQuery()->getResult();
    }

    public function findProcessableSubscriptions(): array
    {
        $qb = $this->createQueryBuilder('q');
        $qb->andWhere('q.state = :state');
        $qb->setParameter('state', MollieSubscriptionInterface::STATE_PROCESSING);
        $qb->andWhere('q.processingState = :processingState');
        $qb->setParameter('processingState', MollieSubscriptionInterface::PROCESSING_STATE_PENDING);

        return $qb->getQuery()->getResult();
    }

    public function findOneByOrderIdAsString(string $orderId): ?MollieSubscriptionInterface
    {
        return $this->findOneByOrderId((int) $orderId);
    }

    public function findOneActiveByOrderToken(string $token): ?MollieSubscriptionInterface
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.orders', 'o', Join::WITH, 'o.tokenValue = :token')
            ->andWhere('s.state NOT IN (:states)')
            ->setParameter('token', $token)
            ->setParameter('states', [MollieSubscriptionInterface::STATE_CANCELED, MollieSubscriptionInterface::STATE_ABORTED])
            ->orderBy('s.createdAt', Criteria::DESC)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }
}
