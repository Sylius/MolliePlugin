<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Core\Model\AdminUserInterface;

class OnboardingWizardStatus implements OnboardingWizardStatusInterface
{
    /** @var int */
    protected $id;

    /** @var AdminUserInterface */
    protected $adminUser;

    /** @var bool */
    protected $completed;

    public function getId(): int
    {
        return $this->id;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): void
    {
        $this->completed = $completed;
    }

    public function getAdminUser(): AdminUserInterface
    {
        return $this->adminUser;
    }

    public function setAdminUser(AdminUserInterface $adminUser): void
    {
        $this->adminUser = $adminUser;
    }
}
