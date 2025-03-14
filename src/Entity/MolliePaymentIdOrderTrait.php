<?php

declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

trait MolliePaymentIdOrderTrait
{
    /** @var string|null */
    protected ?string $molliePaymentId = null;

    /**
     * @return string|null
     */
    public function getMolliePaymentId(): ?string
    {
        return $this->molliePaymentId;
    }

    /**
     * @param string|null $molliePaymentId
     *
     * @return void
     */
    public function setMolliePaymentId(?string $molliePaymentId): void
    {
        $this->molliePaymentId = $molliePaymentId;
    }
}
