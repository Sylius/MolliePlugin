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

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;

interface MollieGatewayConfigRepositoryInterface extends RepositoryInterface
{
    /** @return MollieGatewayConfigInterface[] */
    public function findAllEnabledByGateway(GatewayConfigInterface $gateway): array;

    /** @return MollieGatewayConfigInterface[] */
    public function getExistingAmountLimitsById(int $id): array;
}
