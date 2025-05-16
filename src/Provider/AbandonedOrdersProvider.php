<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider;

use Sylius\MolliePlugin\Repository\Query\AbandonedOrdersQueryInterface;

final readonly class AbandonedOrdersProvider implements AbandonedOrdersProviderInterface
{
    public function __construct(
        private AbandonedOrdersQueryInterface $abandonedOrdersQuery,
    ) {
    }

    public function getAbandonedOrders(\DateTime $dateTime, int $maxResults = 20): iterable
    {
        return $this->abandonedOrdersQuery->getQueryByDateTime($dateTime, $maxResults)->getResult();
    }
}
