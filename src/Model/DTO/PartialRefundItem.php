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

namespace Sylius\MolliePlugin\Model\DTO;

final class PartialRefundItem
{
    private int $id;

    private string $type;

    private int $amountTotal = 0;

    private int $amountRefunded = 0;

    private int $quantity = 1;

    private int $amountToRefund = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAmountTotal(): int
    {
        return $this->amountTotal;
    }

    public function setAmountTotal(int $amountTotal): void
    {
        $this->amountTotal = $amountTotal;
    }

    public function getAmountRefunded(): int
    {
        return $this->amountRefunded;
    }

    public function setAmountRefunded(int $amountRefunded): void
    {
        $this->amountRefunded += $amountRefunded;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getAvailableAmountToRefund(): int
    {
        return $this->getAmountTotal() - $this->getAmountRefunded() - $this->getAmountToRefund();
    }

    public function setAmountToRefund(int $amount): int
    {
        $value = $this->getAvailableAmountToRefund() - $amount;

        if (0 > $value) {
            $this->amountToRefund = $this->getAvailableAmountToRefund();

            return abs($value);
        }

        $this->amountToRefund = $amount;

        return 0;
    }

    public function getAmountToRefund(): int
    {
        return $this->amountToRefund;
    }
}
