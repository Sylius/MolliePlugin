<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory\OnboardingWizard;

use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface StatusFactoryInterface extends FactoryInterface
{
    public function create(AdminUserInterface $adminUser, bool $completed): OnboardingWizardStatusInterface;
}
