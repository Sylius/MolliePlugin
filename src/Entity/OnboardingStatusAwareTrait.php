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

/** @mixin OnboardingStatusAwareInterface */
trait OnboardingStatusAwareTrait
{
    /** @ORM\Column(name="mollie_onboarding_completed", type="boolean", nullable=false, options={"default": false}) */
    #[ORM\Column(name: 'mollie_onboarding_completed', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $mollieOnboardingCompleted = false;

    public function isMollieOnboardingCompleted(): bool
    {
        return $this->mollieOnboardingCompleted;
    }

    public function setMollieOnboardingCompleted(bool $completed): void
    {
        $this->mollieOnboardingCompleted = $completed;
    }
}
