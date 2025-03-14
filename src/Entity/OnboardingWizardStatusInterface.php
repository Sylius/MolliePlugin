<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface OnboardingWizardStatusInterface extends ResourceInterface
{
    public function isCompleted(): bool;

    public function setCompleted(bool $completed): void;

    public function getAdminUser(): AdminUserInterface;

    public function setAdminUser(AdminUserInterface $adminUser): void;
}
