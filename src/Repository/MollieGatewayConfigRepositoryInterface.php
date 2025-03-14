<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Repository;

use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface MollieGatewayConfigRepositoryInterface extends RepositoryInterface
{
    public function findAllEnabledByGateway(GatewayConfigInterface $gateway): array;

    public function getExistingAmountLimitsById(int $id): array;
}
