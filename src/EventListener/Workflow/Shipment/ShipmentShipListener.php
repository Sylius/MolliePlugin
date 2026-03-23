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

namespace Sylius\MolliePlugin\EventListener\Workflow\Shipment;

use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\MolliePlugin\Shipping\MollieShipmentNotifierInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class ShipmentShipListener
{
    public function __construct(
        private MollieShipmentNotifierInterface $notifier,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $shipment = $event->getSubject();
        Assert::isInstanceOf($shipment, ShipmentInterface::class);

        $this->notifier->shipAll($shipment);
    }
}
