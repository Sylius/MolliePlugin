<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\MolliePlugin\DTO\MolliePayment;

class Amount
{
    /**
     * Amount constructor
     */
    public function __construct(private ?string $value, private ?string $currency)
    {
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return void
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string|null $currency
     *
     * @return void
     */
    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->getValue(),
            'currency' => $this->getCurrency()
        ];
    }
}
