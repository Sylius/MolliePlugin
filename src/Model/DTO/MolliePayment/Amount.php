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

namespace Sylius\MolliePlugin\Model\DTO\MolliePayment;

/**
 * @phpstan-type AmountArray array{
 *     value: string|null,
 *     currency: string|null,
 * }
 */
class Amount
{
    public function __construct(private ?string $value, private ?string $currency)
    {
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    /** @return AmountArray */
    public function toArray(): array
    {
        return [
            'value' => $this->getValue(),
            'currency' => $this->getCurrency(),
        ];
    }
}
