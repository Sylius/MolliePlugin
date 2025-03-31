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

namespace SyliusMolliePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @mixin OrderInterface */
trait AbandonedEmailOrderTrait
{
    /**
     * @var bool
     * @ORM\Column(type="boolean", name="abandoned_email")
     */
    #[ORM\Column(name: "abandoned_email", type: "boolean")]
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
