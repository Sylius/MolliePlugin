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
