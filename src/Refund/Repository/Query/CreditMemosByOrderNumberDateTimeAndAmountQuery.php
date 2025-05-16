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

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\RefundPlugin\Repository\CreditMemoRepositoryInterface;

final readonly class CreditMemosByOrderNumberDateTimeAndAmountQuery implements CreditMemosByOrderNumberDateTimeAndAmountQueryInterface
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param CreditMemoRepositoryInterface&EntityRepository $creditMemoRepository
     */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CreditMemoRepositoryInterface $creditMemoRepository,
    ) {
    }

    public function __invoke(string $orderNumber, \DateTime $dateTime, int $amount): iterable
    {
        $order = $this->orderRepository->findOneBy(['number' => $orderNumber]);
        if (null === $order) {
            return [];
        }

        return $this->creditMemoRepository->createQueryBuilder('o')
            ->andWhere('o.order = :orderId')
            ->andWhere('o.issuedAt > :issuedAt')
            ->andWhere('o.total = :amount')
            ->setParameter('orderId', $order->getId())
            ->setParameter('issuedAt', $dateTime)
            ->setParameter('amount', $amount)
            ->getQuery()
            ->getResult()
        ;
    }
}
