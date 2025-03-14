<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\DTO;

final class PartialShipItem
{
    /** @var int */
    private $id;

    /** @var string */
    private $lineId;

    /** @var int */
    private $quantity;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLineId(): string
    {
        return $this->lineId;
    }

    public function setLineId(string $lineId): void
    {
        $this->lineId = $lineId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
