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
trait AbandonedEmailOrderTrait
{
    protected bool $abandonedEmail = false;

    public function isAbandonedEmail(): bool
    {
        return $this->abandonedEmail;
    }

    public function setAbandonedEmail(bool $abandonedEmail): void
    {
        $this->abandonedEmail = $abandonedEmail;
    }
}
