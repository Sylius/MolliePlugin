<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Creator;

use Sylius\MolliePlugin\DTO\PartialRefundItems;
use Sylius\MolliePlugin\Exceptions\OfflineRefundPaymentMethodNotFound;
use Sylius\MolliePlugin\Helper\ConvertOrderInterface;
use Sylius\MolliePlugin\Refund\Units\UnitsItemOrderRefundInterface;
use Sylius\MolliePlugin\Refund\Units\UnitsShipmentOrderRefundInterface;
use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Provider\RefundPaymentMethodsProviderInterface;
use Webmozart\Assert\Assert;

final class OrderRefundCommandCreator implements OrderRefundCommandCreatorInterface
{
    /** @var RepositoryInterface */
    private $orderRepository;

    /** @var UnitsItemOrderRefundInterface */
    private $unitsItemOrderRefund;

    /** @var UnitsShipmentOrderRefundInterface */
    private $shipmentOrderRefund;

    /** @var RefundPaymentMethodsProviderInterface */
    private $refundPaymentMethodProvider;

    public function __construct(
        RepositoryInterface $orderRepository,
        UnitsItemOrderRefundInterface $unitsItemOrderRefund,
        UnitsShipmentOrderRefundInterface $shipmentOrderRefund,
        RefundPaymentMethodsProviderInterface $refundPaymentMethodProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->unitsItemOrderRefund = $unitsItemOrderRefund;
        $this->shipmentOrderRefund = $shipmentOrderRefund;
        $this->refundPaymentMethodProvider = $refundPaymentMethodProvider;
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

            if ('paid' === $line->status && ConvertOrderInterface::PHYSICAL_TYPE === $line->type) {
                if (!property_exists($line, 'metadata') ||
                    !property_exists($line, 'quantityRefunded')
                ) {
                    throw new \InvalidArgumentException();
                }

                $getRefundedQuantity = $this->unitsItemOrderRefund->getActualRefundedQuantity($syliusOrder, $line->metadata->item_id);
                $partialRefundItems->addPartialRefundItemByQuantity(
                    $line->metadata->item_id,
                    $line->type,
                    $line->quantityRefunded - $getRefundedQuantity
                );
            }
        }

        Assert::notNull($syliusOrder->getChannel());
        $refundMethods = $this->refundPaymentMethodProvider->findForChannel($syliusOrder->getChannel());

        if (0 === count($refundMethods)) {
            throw new OfflineRefundPaymentMethodNotFound(
                sprintf('Not found offline payment method on this channel with code :%s', $syliusOrder->getChannel()->getCode())
            );
        }

        $refundMethod = current($refundMethods);

        $unitsToRefund = $this->unitsItemOrderRefund->refund($syliusOrder, $partialRefundItems);
        $shipmentToRefund = $this->shipmentOrderRefund->refund($order, $syliusOrder);

        Assert::notNull($syliusOrder->getNumber());

        return new RefundUnits($syliusOrder->getNumber(), $unitsToRefund, $shipmentToRefund, $refundMethod->getId(), '');
    }
}
