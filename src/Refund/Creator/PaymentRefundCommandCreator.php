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

use Mollie\Api\Resources\Payment;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Exceptions\OfflineRefundPaymentMethodNotFound;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Refund\Units\PaymentUnitsItemRefundInterface;
use Sylius\MolliePlugin\Refund\Units\ShipmentUnitRefundInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Entity\RefundInterface;
use Sylius\RefundPlugin\Provider\RefundPaymentMethodsProviderInterface;
use Webmozart\Assert\Assert;

final class PaymentRefundCommandCreator implements PaymentRefundCommandCreatorInterface
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly RepositoryInterface $refundUnitsRepository,
        private readonly PaymentUnitsItemRefundInterface $itemRefund,
        private readonly ShipmentUnitRefundInterface $shipmentRefund,
        private readonly RefundPaymentMethodsProviderInterface $refundPaymentMethodProvider,
        private readonly DivisorProviderInterface $divisorProvider,
    ) {
    }

    public function fromPayment(Payment $payment): RefundUnits
    {
        $orderId = $payment->metadata->order_id;

        /** @var ?OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        Assert::notNull($order, sprintf('Cannot find order id with id %s', $orderId));

        /** @var RefundInterface[] $allRefunded */
        $allRefunded = $this->refundUnitsRepository->findBy(['order' => $order->getId()]);

        $refunded = $this->getSumOfAmountExistingRefunds($allRefunded);

        Assert::notNull($payment->amountRefunded);
        $mollieRefund = (float) $payment->amountRefunded->value * $this->divisorProvider->getDivisor();
        $toRefund = (int) $mollieRefund - $refunded;

        Assert::notNull($order->getChannel());
        $refundMethods = $this->refundPaymentMethodProvider->findForChannel($order->getChannel());

        if (0 === count($refundMethods)) {
            throw new OfflineRefundPaymentMethodNotFound(
                sprintf(
                    'Not found offline payment method on this channel with code :%s',
                    $order->getChannel()->getCode(),
                ),
            );
        }

        $refundMethod = current($refundMethods);

        $orderItemUnitRefund = $this->itemRefund->refund($order, $toRefund);
        $shipmentRefund = $this->shipmentRefund->refund($order, $orderItemUnitRefund, $toRefund);

        Assert::notNull($order->getNumber());

        return new RefundUnits(
            $order->getNumber(),
            array_merge($orderItemUnitRefund, $shipmentRefund),
            $refundMethod->getId(),
            '',
        );
    }

    /** @param RefundInterface[] $refundedUnits */
    private function getSumOfAmountExistingRefunds(array $refundedUnits): int
    {
        $sum = 0;

        if (0 === count($refundedUnits)) {
            return $sum;
        }

        foreach ($refundedUnits as $refundedUnit) {
            $sum += $refundedUnit->getAmount();
        }

        return $sum;
    }
}
