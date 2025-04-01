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

use Doctrine\ORM\Mapping as ORM;

/** @mixin OrderInterface */
trait MolliePaymentIdOrderTrait
{
    /** @ORM\Column(type="text", name="mollie_payment_id", length=1500, nullable=true) */
    #[ORM\Column(name: 'mollie_payment_id', type: 'text', length: 1500, nullable: true)]
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
