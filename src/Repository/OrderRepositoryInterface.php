<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Repository;

use Sylius\Component\Core\Repository\OrderRepositoryInterface as BaseOrderRepositoryInterface;

interface OrderRepositoryInterface extends BaseOrderRepositoryInterface
{
    public function findAbandonedByDateTime(\DateTime $dateTime): array;
}
