<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

trait QRCodeOrderTrait
{
    /** @var string|null */
    protected ?string $qrCode = null;

    /**
     * @return string|null
     */
    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    /**
     * @param string|null $qrCode
     *
     * @return void
     */
    public function setQrCode(?string $qrCode): void
    {
        $this->qrCode = $qrCode;
    }
}
