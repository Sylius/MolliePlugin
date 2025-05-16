<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Repository\Query;

use Doctrine\ORM\Query;

interface AbandonedOrdersQueryInterface
{
    public function getQueryByDateTime(\DateTime $dateTime, int $maxResults = 20): Query;
}
