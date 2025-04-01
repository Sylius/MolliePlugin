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

/** @mixin ProductVariantInterface */
trait RecurringProductVariantTrait
{
    /** @ORM\Column(type="boolean", name="recurring", nullable="false", options={"default":0}) */
    #[ORM\Column(name: 'recurring', type: 'boolean', nullable: false, options: ['default' => 0])]
    private bool $recurring = false;

    /** @ORM\Column(type="integer", name="recurring_times", nullable="true") */
    #[ORM\Column(name: 'recurring_times', type: 'integer', nullable: true)]
    private ?int $times = null;

    /** @ORM\Column(type="string", name="recurring_interval", nullable="true") */
    #[ORM\Column(name: 'recurring_interval', type: 'string', nullable: true)]
    private ?string $interval = null;

    public function isRecurring(): bool
    {
        return $this->recurring;
    }

    public function setRecurring(bool $recurring): void
    {
        $this->recurring = $recurring;
    }

    public function getTimes(): ?int
    {
        return $this->times;
    }

    public function setTimes(?int $times): void
    {
        $this->times = $times;
    }

    public function getInterval(): ?string
    {
        return $this->interval;
    }

    public function setInterval(?string $interval): void
    {
        $this->interval = $interval;
    }
}
