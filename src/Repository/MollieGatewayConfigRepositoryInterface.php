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

namespace Sylius\MolliePlugin\Repository;

use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface MollieGatewayConfigRepositoryInterface extends RepositoryInterface
{
    public function findAllEnabledByGateway(GatewayConfigInterface $gateway): array;

    public function getExistingAmountLimitsById(int $id): array;
}
