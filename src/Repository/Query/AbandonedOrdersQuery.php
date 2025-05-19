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
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final readonly class AbandonedOrdersQuery implements AbandonedOrdersQueryInterface
{
    /** @param OrderRepositoryInterface&EntityRepository $orderRepository */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(\DateTime $dateTime, int $maxResults = 20): iterable
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
            ->getResult()
        ;
    }
}
