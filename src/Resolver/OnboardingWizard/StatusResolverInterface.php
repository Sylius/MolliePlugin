<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\OnboardingWizard;

use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;
use Sylius\Component\Core\Model\AdminUserInterface;

interface StatusResolverInterface
{
    public function resolve(AdminUserInterface $adminUser): OnboardingWizardStatusInterface;
}
