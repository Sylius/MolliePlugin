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

namespace Tests\SyliusMolliePlugin\Entity;

use SyliusMolliePlugin\Entity\AbandonedEmailOrderTrait;
use SyliusMolliePlugin\Entity\MolliePaymentIdOrderTrait;
use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Entity\QRCodeOrderTrait;
use SyliusMolliePlugin\Entity\RecurringOrderTrait;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\Component\Core\Model\OrderItemInterface;

class Order extends BaseOrder implements OrderInterface
{
    use AbandonedEmailOrderTrait;

    use RecurringOrderTrait;

    use QRCodeOrderTrait;

    use MolliePaymentIdOrderTrait;

    public function getRecurringItems(): Collection
    {
        return $this
            ->items
            ->filter(function (OrderItemInterface $orderItem) {
                $variant = $orderItem->getVariant();

                return $variant !== null
                    && true === $variant->isRecurring();
            })
        ;
    }

    public function getNonRecurringItems(): Collection
    {
        return $this
            ->items
            ->filter(function (OrderItemInterface $orderItem) {
                $variant = $orderItem->getVariant();

                return $variant !== null
                    && false === $variant->isRecurring();
            })
        ;
    }

    public function hasRecurringContents(): bool
    {
        return 0 < $this->getRecurringItems()->count();
    }

    public function hasNonRecurringContents(): bool
    {
        return 0 < $this->getNonRecurringItems()->count();
    }
}
