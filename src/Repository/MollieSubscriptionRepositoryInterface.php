<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Repository;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface MollieSubscriptionRepositoryInterface extends RepositoryInterface
{
    public function findOneByOrderId(int $orderId): ?MollieSubscriptionInterface;

    /** @return MollieSubscriptionInterface[] */
    public function findByOrderId(int $orderId): array;

    /** @return MollieSubscriptionInterface[] */
    public function findByPayment(PaymentInterface $payment): array;

    /** @return MollieSubscriptionInterface[] */
    public function findScheduledSubscriptions(): array;

    /** @return MollieSubscriptionInterface[] */
    public function findProcessableSubscriptions(): array;

    public function findOneByOrderIdAsString(string $orderId): ?MollieSubscriptionInterface;
}
