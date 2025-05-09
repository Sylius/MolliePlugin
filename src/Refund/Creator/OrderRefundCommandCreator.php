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

namespace Sylius\MolliePlugin\Refund\Creator;

use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Converter\OrderConverterInterface;
use Sylius\MolliePlugin\Exceptions\OfflineRefundPaymentMethodNotFound;
use Sylius\MolliePlugin\Model\DTO\PartialRefundItems;
use Sylius\MolliePlugin\Refund\Units\UnitsItemOrderRefundInterface;
use Sylius\MolliePlugin\Refund\Units\UnitsShipmentOrderRefundInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Provider\RefundPaymentMethodsProviderInterface;
use Webmozart\Assert\Assert;

final class OrderRefundCommandCreator implements OrderRefundCommandCreatorInterface
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly UnitsItemOrderRefundInterface $unitsItemOrderRefund,
        private readonly UnitsShipmentOrderRefundInterface $shipmentOrderRefund,
        private readonly RefundPaymentMethodsProviderInterface $refundPaymentMethodProvider,
    ) {
    }

    public function fromOrder(Order $order): RefundUnits
    {
        $orderId = $order->metadata->order_id;
        /** @var OrderInterface $syliusOrder */
        $syliusOrder = $this->orderRepository->findOneBy(['id' => $orderId]);

        $partialRefundItems = new PartialRefundItems();

        foreach ($order->lines as $line) {
            if (!property_exists($line, 'status') ||
                !property_exists($line, 'type')
            ) {
                throw new \InvalidArgumentException();
            }

            if ('paid' === $line->status && OrderConverterInterface::PHYSICAL_TYPE === $line->type) {
                if (!property_exists($line, 'metadata') ||
                    !property_exists($line, 'quantityRefunded')
                ) {
                    throw new \InvalidArgumentException();
                }

                $getRefundedQuantity = $this->unitsItemOrderRefund->getActualRefundedQuantity(
                    $syliusOrder,
                    $line->metadata->item_id,
                );
                $partialRefundItems->addPartialRefundItemByQuantity(
                    $line->metadata->item_id,
                    $line->type,
                    $line->quantityRefunded - $getRefundedQuantity,
                );
            }
        }

        $refundMethods = $this->refundPaymentMethodProvider->findForOrder($syliusOrder);

        if (0 === count($refundMethods)) {
            throw new OfflineRefundPaymentMethodNotFound(
                sprintf(
                    'Not found offline payment method on this channel with code :%s',
                    $syliusOrder->getChannel()->getCode(),
                ),
            );
        }

        $refundMethod = current($refundMethods);

        $unitsToRefund = $this->unitsItemOrderRefund->refund($syliusOrder, $partialRefundItems);
        $shipmentToRefund = $this->shipmentOrderRefund->refund($order, $syliusOrder);

        Assert::notNull($syliusOrder->getNumber());

        return new RefundUnits(
            $syliusOrder->getNumber(),
            array_merge($unitsToRefund, $shipmentToRefund),
            $refundMethod->getId(),
            '',
        );
    }
}
