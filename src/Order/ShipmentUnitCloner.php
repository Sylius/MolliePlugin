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

namespace Sylius\MolliePlugin\Order;

use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Shipping\Model\ShipmentUnitInterface;

final class ShipmentUnitCloner implements ShipmentUnitClonerInterface
{
    private FactoryInterface $unitFactory;

    public function __construct(FactoryInterface $unitFactory)
    {
        $this->unitFactory = $unitFactory;
    }

    public function clone(ShipmentUnitInterface $unit): ShipmentUnitInterface
    {
        /** @var ShipmentUnitInterface $cloned */
        $cloned = $this->unitFactory->createNew();

        $cloned->setCreatedAt(new \DateTime());

        return $cloned;
    }
}
