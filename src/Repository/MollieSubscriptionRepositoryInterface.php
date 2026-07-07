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

namespace Sylius\MolliePlugin\Repository;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

/**
 * @deprecated since Mollie 3.3 and will be removed in 4.0.
 *
 * @see https://github.com/Sylius/MolliePlugin/blob/3.3/UPGRADE-3.3.md for migration details
 */
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
    public function findScheduledSubscriptionsForMigration(): array;

    /** @return MollieSubscriptionInterface[] */
    public function findProcessableSubscriptions(): array;

    /** @return iterable<MollieSubscriptionInterface> */
    public function iterateToMigrate(int $batchSize): iterable;

    /** @return MollieSubscriptionInterface[] */
    public function findMigrated(int $limit): array;

    public function findOneByOrderIdAsString(string $orderId): ?MollieSubscriptionInterface;

    public function findOneActiveByOrderToken(string $token): ?MollieSubscriptionInterface;
}
