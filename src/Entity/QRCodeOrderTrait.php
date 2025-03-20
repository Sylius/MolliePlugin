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

/** @mixin OrderInterface */
trait QRCodeOrderTrait
{
    protected ?string $qrCode = null;

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(?string $qrCode): void
    {
        $this->qrCode = $qrCode;
    }
}
