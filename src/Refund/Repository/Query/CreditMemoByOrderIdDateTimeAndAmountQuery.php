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

namespace Sylius\MolliePlugin\Refund\Repository\Query;

use Doctrine\ORM\Query;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\RefundPlugin\Repository\CreditMemoRepositoryInterface;

final readonly class CreditMemoByOrderIdDateTimeAndAmountQuery implements CreditMemoByOrderIdDateTimeAndAmountQueryInterface
{
    /** @param CreditMemoRepositoryInterface&EntityRepository $creditMemoRepository */
    public function __construct(
        private CreditMemoRepositoryInterface $creditMemoRepository,
    ) {
    }

    public function getQuery(int $orderId, \DateTime $dateTime, int $amount): Query
    {
        return $this->creditMemoRepository->createQueryBuilder('o')
            ->andWhere('o.order = :orderId')
            ->andWhere('o.issuedAt > :issuedAt')
            ->andWhere('o.total = :amount')
            ->setParameter('orderId', $orderId)
            ->setParameter('issuedAt', $dateTime)
            ->setParameter('amount', $amount)
            ->getQuery()
        ;
    }
}
