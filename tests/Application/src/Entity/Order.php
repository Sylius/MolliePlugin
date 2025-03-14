<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Entity;

use Sylius\MolliePlugin\Entity\AbandonedEmailOrderTrait;
use Sylius\MolliePlugin\Entity\MolliePaymentIdOrderTrait;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\QRCodeOrderTrait;
use Sylius\MolliePlugin\Entity\RecurringOrderTrait;
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
