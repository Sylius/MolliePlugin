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

namespace Sylius\MolliePlugin\Voucher\Updater;

use Mollie\Api\Resources\Payment;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Voucher\Applicator\OrderVouchersApplicatorInterface;

final class OrderVoucherAdjustmentUpdater implements OrderVoucherAdjustmentUpdaterInterface
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly OrderVouchersApplicatorInterface $orderVouchersApplicator,
        private readonly DivisorProviderInterface $divisorProvider,
    ) {
    }

    public function update(Payment $molliePayment, int $orderId): void
    {
        $amount = 0;

        /** @var OrderInterface $order */
        $order = $this->orderRepository->find($orderId);

        if (isset($molliePayment->details->vouchers)) {
            foreach ($molliePayment->details->vouchers as $voucher) {
                $amount += (float) $voucher->amount->value;
            }
        }

        $amount = (int) ($amount * $this->divisorProvider->getDivisor());

        $this->orderVouchersApplicator->distribute($order, $amount);
    }
}
