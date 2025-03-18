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

namespace Sylius\MolliePlugin\Updater\Order;

use Mollie\Api\Resources\Payment;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Applicator\Order\OrderVouchersApplicatorInterface;
use Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface;

final class OrderVoucherAdjustmentUpdater implements OrderVoucherAdjustmentUpdaterInterface
{
    /** @var RepositoryInterface */
    private $orderRepository;

    /** @var AdjustmentFactoryInterface */
    private $adjustmentFactory;

    /** @var OrderVouchersApplicatorInterface */
    private $orderVouchersApplicator;

    /** @var DivisorProviderInterface */
    private $divisorProvider;

    public function __construct(
        RepositoryInterface              $orderRepository,
        AdjustmentFactoryInterface       $adjustmentFactory,
        OrderVouchersApplicatorInterface $orderVouchersApplicator,
        DivisorProviderInterface         $divisorProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->adjustmentFactory = $adjustmentFactory;
        $this->orderVouchersApplicator = $orderVouchersApplicator;
        $this->divisorProvider = $divisorProvider;
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
