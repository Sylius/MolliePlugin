<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Repository\Query;

use Doctrine\ORM\Query;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final readonly class AbandonedOrdersQuery implements AbandonedOrdersQueryInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function getQueryByDateTime(\DateTime $dateTime, int $maxResults = 20): Query
    {
        return $this->orderRepository->createQueryBuilder('o')
            ->where('o.paymentState = :paymentState')
            ->andWhere('o.state = :state')
            ->andWhere('o.createdAt <= :createdAt')
            ->andWhere('o.abandonedEmail = :abandonedEmail')
            ->setParameter('state', OrderInterface::STATE_NEW)
            ->setParameter('paymentState', OrderPaymentStates::STATE_AWAITING_PAYMENT)
            ->setParameter('createdAt', $dateTime)
            ->setParameter('abandonedEmail', false)
            ->setMaxResults($maxResults)
            ->getQuery()
        ;
    }
}
