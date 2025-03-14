<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

trait AbandonedEmailOrderTrait
{
    /** @var bool */
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
