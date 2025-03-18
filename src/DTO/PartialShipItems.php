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

namespace SyliusMolliePlugin\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class PartialShipItems
{
    /** @var Collection */
    private $partialShipItems;

    public function __construct()
    {
        $this->partialShipItems = new ArrayCollection();
    }

    public function setPartialShipItem(PartialShipItem $partialShipItem): void
    {
        $partialItem = $this->findPartialShipItemWhereItem($partialShipItem);

        if (null !== $partialItem) {
            $partialItem->setQuantity($partialItem->getQuantity() + 1);

            return;
        }

        $this->partialShipItems->add($partialShipItem);
    }

    public function getPartialShipItems(): Collection
    {
        return $this->partialShipItems;
    }

    public function getArrayFromObject(): array
    {
        $data = [];
        /** @var PartialShipItem $item */
        foreach ($this->getPartialShipItems() as $item) {
            $data[] = [
                'id' => $item->getLineId(),
                'quantity' => $item->getQuantity(),
            ];
        }

        return $data;
    }

    public function findPartialShipItemWhereItem(PartialShipItem $partialShipItem): ?PartialShipItem
    {
        /** @var PartialShipItem $item */
        foreach ($this->getPartialShipItems() as $item) {
            if ($item->getId() === $partialShipItem->getId()) {
                return $item;
            }
        }

        return null;
    }

    public function findById(int $id): ?PartialShipItem
    {
        /** @var PartialShipItem $item */
        foreach ($this->getPartialShipItems() as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        return null;
    }
}
