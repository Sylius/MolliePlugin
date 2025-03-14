<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class PartialRefundItems
{
    /** @var Collection */
    private $partialRefundItems;

    public function __construct()
    {
        $this->partialRefundItems = new ArrayCollection();
    }

    public function getPartialRefundItems(): Collection
    {
        return $this->partialRefundItems;
    }

    public function setPartialRefundItems(PartialRefundItem $partialRefundItem): void
    {
        $this->partialRefundItems->add($partialRefundItem);
    }

    public function addPartialRefundItemByQuantity(
        int $id,
        string $type,
        int $quantity
    ): void {
        for ($oneItem = 0; $oneItem < $quantity; ++$oneItem) {
            $partialRefundItem = new PartialRefundItem();
            $partialRefundItem->setId($id);
            $partialRefundItem->setType($type);

            $this->partialRefundItems->add($partialRefundItem);
        }
    }

    public function findById(int $id): ?PartialRefundItem
    {
        foreach ($this->getPartialRefundItems() as $item) {
            if ($id === $item->getId()) {
                return $item;
            }
        }

        return null;
    }

    public function removeItem(PartialRefundItem $partialRefundItem): void
    {
        $this->getPartialRefundItems()->removeElement($partialRefundItem);
    }
}
