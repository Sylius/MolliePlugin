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

namespace Tests\Sylius\MolliePlugin\Unit\EventListener\Workflow\Shipment;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\MolliePlugin\EventListener\Workflow\Shipment\ShipmentShipListener;
use Sylius\MolliePlugin\Shipping\MollieShipmentNotifierInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;

final class ShipmentShipListenerTest extends TestCase
{
    public function testDelegatesToNotifier(): void
    {
        $notifier = $this->createMock(MollieShipmentNotifierInterface::class);
        $shipment = $this->createMock(ShipmentInterface::class);

        $notifier->expects($this->once())
            ->method('shipAll')
            ->with($shipment);

        $listener = new ShipmentShipListener($notifier);

        ($listener)(new CompletedEvent($shipment, new Marking()));
    }
}
