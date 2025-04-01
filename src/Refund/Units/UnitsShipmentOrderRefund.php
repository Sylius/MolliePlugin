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

namespace Sylius\MolliePlugin\Refund\Units;

use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\Adjustment;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\RefundPlugin\Model\RefundType;
use Sylius\RefundPlugin\Model\ShipmentRefund;

final class UnitsShipmentOrderRefund implements UnitsShipmentOrderRefundInterface
{
    public function __construct(private readonly RepositoryInterface $refundUnitsRepository)
    {
    }

    public function refund(Order $order, OrderInterface $syliusOrder): array
    {
        if ($this->hasShipmentRefund($syliusOrder)) {
            return [];
        }

        /** @var mixed $line */
        foreach ($order->lines as $line) {
            if (!property_exists($line, 'type') ||
                !property_exists($line, 'refundableQuantity')) {
                throw new \InvalidArgumentException();
            }

            if (OrderConverterInterface::SHIPPING_TYPE === $line->type && 0 < $line->quantityRefunded) {
                /** @var Adjustment $refundedShipment */
                $refundedShipment = $syliusOrder->getAdjustments('shipping')->first();

                return [
                    new ShipmentRefund(
                        $refundedShipment->getId(),
                        $refundedShipment->getAmount(),
                    ),
                ];
            }
        }

        return [];
    }

    private function hasShipmentRefund(OrderInterface $order): bool
    {
        $unitRefunded = $this->refundUnitsRepository->findOneBy([
            'order' => $order->getId(),
            'type' => RefundType::shipment(),
        ]);

        return null !== $unitRefunded;
    }
}
