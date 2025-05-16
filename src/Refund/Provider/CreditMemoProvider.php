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

namespace Sylius\MolliePlugin\Refund\Provider;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Refund\Repository\Query\CreditMemoByOrderIdDateTimeAndAmountQuery;

final readonly class CreditMemoProvider implements CreditMemoProviderInterface
{
    /** @param OrderRepositoryInterface<OrderInterface> $orderRepository */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CreditMemoByOrderIdDateTimeAndAmountQuery $query,
    ) {
    }

    public function getByOrderNumberDateTimeAndAmount(
        string $orderNumber,
        \DateTime $dateTime,
        int $amount,
    ): iterable {
        $order = $this->orderRepository->findOneBy(['number' => $orderNumber]);
        if (null === $order) {
            return [];
        }

        return $this->query->getQuery($order->getId(), $dateTime, $amount)->getResult();
    }
}
