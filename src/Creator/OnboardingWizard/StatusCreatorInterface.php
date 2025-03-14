<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Creator\OnboardingWizard;

use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;

interface StatusCreatorInterface
{
    public function create(): OnboardingWizardStatusInterface;
}
