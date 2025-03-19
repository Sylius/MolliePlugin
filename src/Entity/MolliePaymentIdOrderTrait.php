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

namespace Sylius\MolliePlugin\Entity;

trait MolliePaymentIdOrderTrait
{
    protected ?string $molliePaymentId = null;

    public function getMolliePaymentId(): ?string
    {
        return $this->molliePaymentId;
    }

    public function setMolliePaymentId(?string $molliePaymentId): void
    {
        $this->molliePaymentId = $molliePaymentId;
    }
}
