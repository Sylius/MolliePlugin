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

namespace Sylius\MolliePlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\OrderInterface as SyliusOrder;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface MollieSubscriptionInterface extends ResourceInterface
{
    public const STATE_NEW = 'new';

    public const STATE_ACTIVE = 'active';

    public const STATE_PROCESSING = 'processing';

    public const STATE_PAUSED = 'paused';

    public const STATE_CANCELED = 'canceled';

    public const STATE_COMPLETED = 'completed';

    public const STATE_ABORTED = 'aborted';

    public const PROCESSING_STATE_NONE = 'none';

    public const PROCESSING_STATE_PENDING = 'pending';

    public const PROCESSING_STATE_PROCESSING = 'processing';

    public const PROCESSING_STATE_PROCESSED = 'processed';

    public const PAYMENT_STATE_PENDING = 'pending';

    public const PAYMENT_STATE_OK = 'ok';

    public const PAYMENT_STATE_FAIL = 'fail';

    public function getState(): string;

    public function setState(string $state): void;

    /** @return Collection<int, PaymentInterface> */
    public function getPayments(): Collection;

    public function getLastPayment(): ?PaymentInterface;

    public function addPayment(PaymentInterface $payment): void;

    /** @return Collection<int, SyliusOrder> */
    public function getOrders(): Collection;

    public function addOrder(SyliusOrder $order): void;

    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(CustomerInterface $user): void;

    public function getOrderItem(): OrderItemInterface;

    public function setOrderItem(OrderItemInterface $orderItem): void;

    public function getCreatedAt(): \DateTime;

    public function getStartedAt(): ?\DateTime;

    public function setStartedAt(?\DateTime $startedAt = null): void;

    /** @return Collection<int, MollieSubscriptionScheduleInterface> */
    public function getSchedules(): Collection;

    public function addSchedule(MollieSubscriptionScheduleInterface $schedule): void;

    public function removeSchedule(MollieSubscriptionScheduleInterface $schedule): void;

    public function getFirstOrder(): ?OrderInterface;

    public function getProcessingState(): string;

    public function setProcessingState(string $processingState): void;

    public function getScheduleByIndex(int $index): ?MollieSubscriptionScheduleInterface;

    public function getPaymentState(): string;

    public function setPaymentState(string $paymentState): void;

    public function getRecentFailedPaymentsCount(): int;

    public function incrementFailedPaymentCounter(): void;

    public function resetFailedPaymentCount(): void;

    public function getSubscriptionConfiguration(): MollieSubscriptionConfigurationInterface;

    public function getLastOrder(): ?SyliusOrder;
}
