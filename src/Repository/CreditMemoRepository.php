<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Repository;

use Sylius\RefundPlugin\Doctrine\ORM\CreditMemoRepository as BaseCreditMemoRepository;

class CreditMemoRepository extends BaseCreditMemoRepository implements CreditMemoRepositoryInterface
{
    public function findByOrderNumberAndDateTime(
        int $orderId,
        \DateTime $dateTime,
        int $amount
    ): array {
        return $this->createQueryBuilder('o')
            ->andWhere('o.order = :orderId')
            ->andWhere('o.issuedAt > :issuedAt')
            ->andWhere('o.total = :amount')
            ->setParameter('orderId', $orderId)
            ->setParameter('issuedAt', $dateTime)
            ->setParameter('amount', $amount)
            ->getQuery()
            ->getResult()
        ;
    }
}
