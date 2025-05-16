<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider;

use Sylius\Component\Core\Model\OrderInterface;

interface AbandonedOrdersProviderInterface
{
    /** @return iterable<OrderInterface> */
    public function getAbandonedOrders(\DateTime $dateTime, int $maxResults = 20): iterable;
}
